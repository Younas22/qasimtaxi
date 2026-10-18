<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Driver;
use App\Support\GeoMath;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Works out "where is this booking's driver dispatching from, and how far
 * / how long until they can reach the pickup" — the three scenarios are:
 *
 *   1. Driver idle at the office (no fresh GPS ping) — dispatch from HQ.
 *   2. Driver idle away from the office (fresh GPS ping, no active ride).
 *   3. Driver currently mid-ride on another booking — dispatch after they
 *      finish, from wherever that ride drops off.
 *
 * Also powers "suggest the best driver" for a booking that has none yet.
 */
class DriverDispatchService
{
    /**
     * Re-use a booking's last computed dispatch distance/duration as long
     * as it was calculated recently and the driver hasn't moved much since
     * — avoids hitting Google on every poll while a driver sits still.
     */
    private const SNAPSHOT_TTL_SECONDS = 60;

    private const SNAPSHOT_STALE_METERS = 200;

    public function __construct(
        private readonly GoogleMapsService $googleMaps,
        private readonly OfficeLocationService $officeLocation,
    ) {}

    /**
     * @return array<string, mixed>|null Null when the booking has no driver assigned yet.
     */
    public function dispatchInfoFor(Booking $booking): ?array
    {
        $driver = $booking->driver;

        if (! $driver) {
            return null;
        }

        $activeRide = $driver->activeBooking();

        if ($activeRide && $activeRide->id === $booking->id) {
            return $this->inProgressScenario($booking, $driver);
        }

        if ($activeRide) {
            return $this->busyScenario($booking, $driver, $activeRide);
        }

        if ($driver->hasFreshLocation()) {
            return $this->awayScenario($booking, $driver);
        }

        return $this->officeScenario($booking, $driver);
    }

    /**
     * @return array{driver: Driver, distance_km: float, duration_minutes: int}|null
     */
    public function selectBestDriver(Booking $booking): ?array
    {
        if (! $booking->pickup_lat || ! $booking->pickup_lng) {
            return null;
        }

        $onlineDrivers = Driver::active()->where('is_online', true)->get();

        $available = $onlineDrivers->filter(fn (Driver $d) => $d->is_available && ! $d->activeBooking())->values();
        $busy = $onlineDrivers->reject(fn (Driver $d) => $available->contains('id', $d->id))->values();

        if ($available->isNotEmpty()) {
            return $this->nearestAvailable($booking, $available);
        }

        if ($busy->isEmpty()) {
            return null;
        }

        return $this->soonestBusy($booking, $busy);
    }

    /**
     * @return array{driver: Driver, distance_km: float, duration_minutes: int}|null
     */
    private function nearestAvailable(Booking $booking, Collection $drivers): ?array
    {
        $best = null;

        foreach ($drivers as $driver) {
            $origin = $this->dispatchPointFor($driver);

            if (! $origin) {
                continue;
            }

            $result = $this->googleMaps->distance(
                $origin['lat'],
                $origin['lng'],
                (float) $booking->pickup_lat,
                (float) $booking->pickup_lng
            );

            if (! $result) {
                continue;
            }

            if (! $best || $result['duration_minutes'] < $best['duration_minutes']) {
                $best = [
                    'driver' => $driver,
                    'status' => 'available',
                    'distance_km' => $result['distance_km'],
                    'duration_minutes' => $result['duration_minutes'],
                ];
            }
        }

        return $best;
    }

    /**
     * @return array{driver: Driver, distance_km: float, duration_minutes: int}|null
     */
    private function soonestBusy(Booking $booking, Collection $drivers): ?array
    {
        $best = null;
        $bestRideEndsIn = null;

        // Primary criterion is purely "whose current ride ends soonest" (per
        // spec's example: Driver A ends in 12min vs Driver B ends in 26min
        // → choose A) — the post-ride ETA to the new pickup is calculated
        // only for display, not for the selection itself.
        foreach ($drivers as $driver) {
            $activeRide = $driver->activeBooking();

            if (! $activeRide || ! $activeRide->estimated_arrival_at) {
                continue;
            }

            $rideEndsInMinutes = $activeRide->ride_ends_in_minutes;

            if ($bestRideEndsIn === null || $rideEndsInMinutes < $bestRideEndsIn) {
                $postRideEta = null;

                if ($activeRide->dropoff_lat && $activeRide->dropoff_lng) {
                    $postRideEta = $this->googleMaps->distance(
                        (float) $activeRide->dropoff_lat,
                        (float) $activeRide->dropoff_lng,
                        (float) $booking->pickup_lat,
                        (float) $booking->pickup_lng
                    );
                }

                $bestRideEndsIn = $rideEndsInMinutes;
                $best = [
                    'driver' => $driver,
                    'status' => 'busy',
                    'ride_ends_in_minutes' => $rideEndsInMinutes,
                    'distance_km' => $postRideEta['distance_km'] ?? 0.0,
                    'duration_minutes' => $rideEndsInMinutes + (int) ($postRideEta['duration_minutes'] ?? 0),
                ];
            }
        }

        return $best;
    }

    /**
     * The driver is currently driving THIS booking — dispatch-to-pickup
     * info no longer applies (they've already reached, or are en route to,
     * the customer), so this is a distinct display from the other three.
     */
    private function inProgressScenario(Booking $booking, Driver $driver): array
    {
        return [
            'scenario' => 4,
            'driver_status' => 'in_progress',
            'message' => __('Your ride is currently in progress.'),
            'driver' => $this->driverPayload($driver),
            'map' => null,
        ];
    }

    private function officeScenario(Booking $booking, Driver $driver): array
    {
        $office = $this->officeLocation->coordinates();

        $eta = $office
            ? $this->distanceToPickup($booking, $office['lat'], $office['lng'])
            : null;

        return [
            'scenario' => 1,
            'driver_status' => 'available',
            'dispatch_location_label' => __('Head Office'),
            'distance_km' => $eta['distance_km'] ?? null,
            'duration_minutes' => $eta['duration_minutes'] ?? null,
            'message' => __('Driver will depart from our office after booking confirmation.'),
            'driver' => $this->driverPayload($driver),
            'map' => [
                'office' => $office,
                'driver' => null,
                'pickup' => ['lat' => (float) $booking->pickup_lat, 'lng' => (float) $booking->pickup_lng],
                'show_route' => true,
                'route_from' => $office,
            ],
        ];
    }

    private function awayScenario(Booking $booking, Driver $driver): array
    {
        $eta = $this->distanceToPickup($booking, (float) $driver->current_lat, (float) $driver->current_lng);

        return [
            'scenario' => 2,
            'driver_status' => 'available',
            'dispatch_location_label' => __('Current Driver Location'),
            'distance_km' => $eta['distance_km'] ?? null,
            'duration_minutes' => $eta['duration_minutes'] ?? null,
            'message' => __('Driver is currently near your pickup location.'),
            'driver' => $this->driverPayload($driver),
            'map' => [
                'office' => null,
                'driver' => ['lat' => (float) $driver->current_lat, 'lng' => (float) $driver->current_lng],
                'pickup' => ['lat' => (float) $booking->pickup_lat, 'lng' => (float) $booking->pickup_lng],
                'show_route' => true,
                'route_from' => ['lat' => (float) $driver->current_lat, 'lng' => (float) $driver->current_lng],
            ],
        ];
    }

    private function busyScenario(Booking $booking, Driver $driver, Booking $activeRide): array
    {
        $rideEndsInMinutes = $activeRide->ride_ends_in_minutes;

        $currentDistance = $driver->hasFreshLocation()
            ? $this->googleMaps->distance(
                (float) $driver->current_lat,
                (float) $driver->current_lng,
                (float) $booking->pickup_lat,
                (float) $booking->pickup_lng
            )
            : null;

        $postRideEta = null;

        if ($activeRide->dropoff_lat && $activeRide->dropoff_lng) {
            $postRideEta = $this->googleMaps->distance(
                (float) $activeRide->dropoff_lat,
                (float) $activeRide->dropoff_lng,
                (float) $booking->pickup_lat,
                (float) $booking->pickup_lng
            );
        }

        $arrivalAfterDispatch = $rideEndsInMinutes !== null
            ? $rideEndsInMinutes + (int) ($postRideEta['duration_minutes'] ?? 0)
            : null;

        return [
            'scenario' => 3,
            'driver_status' => 'busy',
            'current_ride_label' => Str::limit($activeRide->pickup_location, 24).' → '.Str::limit($activeRide->dropoff_location, 24),
            'ride_ends_in_minutes' => $rideEndsInMinutes,
            'current_driver_distance_km' => $currentDistance['distance_km'] ?? null,
            'dispatch_after_ride_minutes' => $rideEndsInMinutes,
            'arrival_after_dispatch_minutes' => $arrivalAfterDispatch,
            'message' => $rideEndsInMinutes !== null
                ? __('Driver is completing another trip. Estimated availability :minutes minutes.', ['minutes' => $rideEndsInMinutes])
                : __('Driver is completing another trip.'),
            'driver' => $this->driverPayload($driver),
            'map' => null,
        ];
    }

    /**
     * @return array{lat: float, lng: float}|null
     */
    private function dispatchPointFor(Driver $driver): ?array
    {
        if ($driver->hasFreshLocation()) {
            return ['lat' => (float) $driver->current_lat, 'lng' => (float) $driver->current_lng];
        }

        return $this->officeLocation->coordinates();
    }

    /**
     * @return array{distance_km: float, duration_minutes: int}|null
     */
    private function distanceToPickup(Booking $booking, float $originLat, float $originLng): ?array
    {
        if (
            $booking->dispatch_calculated_at
            && $booking->dispatch_calculated_at->gt(now()->subSeconds(self::SNAPSHOT_TTL_SECONDS))
            && $booking->dispatch_lat !== null
            && $booking->dispatch_lng !== null
            && GeoMath::metersBetween((float) $booking->dispatch_lat, (float) $booking->dispatch_lng, $originLat, $originLng) < self::SNAPSHOT_STALE_METERS
        ) {
            return [
                'distance_km' => (float) $booking->dispatch_distance_km,
                'duration_minutes' => (int) $booking->dispatch_duration_minutes,
            ];
        }

        $result = $this->googleMaps->distance($originLat, $originLng, (float) $booking->pickup_lat, (float) $booking->pickup_lng);

        if ($result) {
            $booking->update([
                'dispatch_distance_km' => $result['distance_km'],
                'dispatch_duration_minutes' => $result['duration_minutes'],
                'dispatch_lat' => $originLat,
                'dispatch_lng' => $originLng,
                'dispatch_calculated_at' => now(),
            ]);
        }

        return $result;
    }

    /**
     * @return array{id: int, name: string, phone: ?string, photo_url: ?string, vehicle: ?string}
     */
    private function driverPayload(Driver $driver): array
    {
        return [
            'id' => $driver->id,
            'name' => $driver->name,
            'phone' => $driver->phone,
            'photo_url' => $driver->photo_url,
            'vehicle' => $driver->vehicle?->category?->name ?? $driver->vehicle?->name,
        ];
    }
}
