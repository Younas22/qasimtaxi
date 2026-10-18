<?php

namespace App\Services;

use App\Models\Driver;
use App\Models\PricingRule;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Cache;

/**
 * Works out how far a driver has to travel to reach the pickup, for the
 * pricing rule's approach charge. Short rides (under the rule's
 * approach_driver_max_km) measure from the nearest free, online driver's
 * live GPS position; longer rides — or when no such driver has a fresh
 * location — measure from the office.
 *
 * The distance used for a quote is remembered briefly so the booking the
 * customer submits a moment later is charged exactly what they were shown,
 * even though the driver has moved since.
 */
class ApproachDistanceService
{
    private const QUOTE_LOCK_MINUTES = 30;

    public function __construct(
        private readonly GoogleMapsService $maps,
        private readonly OfficeLocationService $officeLocation
    ) {}

    public function forQuote(Vehicle $vehicle, mixed $pickupLat, mixed $pickupLng, ?float $rideDistanceKm): ?float
    {
        if (! $this->applies($vehicle, $pickupLat, $pickupLng)) {
            return null;
        }

        $approachKm = $this->resolve($vehicle, (float) $pickupLat, (float) $pickupLng, $rideDistanceKm);

        Cache::put($this->lockKey($vehicle, (float) $pickupLat, (float) $pickupLng), $approachKm, now()->addMinutes(self::QUOTE_LOCK_MINUTES));

        return $approachKm;
    }

    public function forBooking(Vehicle $vehicle, mixed $pickupLat, mixed $pickupLng, ?float $rideDistanceKm): ?float
    {
        if (! $this->applies($vehicle, $pickupLat, $pickupLng)) {
            return null;
        }

        $key = $this->lockKey($vehicle, (float) $pickupLat, (float) $pickupLng);

        if (Cache::has($key)) {
            return Cache::get($key);
        }

        return $this->resolve($vehicle, (float) $pickupLat, (float) $pickupLng, $rideDistanceKm);
    }

    private function applies(Vehicle $vehicle, mixed $pickupLat, mixed $pickupLng): bool
    {
        return $pickupLat !== null
            && $pickupLng !== null
            && (float) PricingRule::resolveForVehicle($vehicle)->approach_km_fare > 0;
    }

    private function resolve(Vehicle $vehicle, float $pickupLat, float $pickupLng, ?float $rideDistanceKm): ?float
    {
        $driverMaxKm = (float) PricingRule::resolveForVehicle($vehicle)->approach_driver_max_km;

        if ($driverMaxKm > 0 && ($rideDistanceKm ?? 0) < $driverMaxKm) {
            $fromDriver = $this->nearestDriverKm($vehicle, $pickupLat, $pickupLng);

            if ($fromDriver !== null) {
                return $fromDriver;
            }
        }

        $office = $this->officeLocation->coordinates();

        return $office ? $this->drivingKm($office['lat'], $office['lng'], $pickupLat, $pickupLng) : null;
    }

    private function nearestDriverKm(Vehicle $vehicle, float $pickupLat, float $pickupLng): ?float
    {
        $nearest = null;

        $drivers = Driver::active()
            ->where('is_online', true)
            ->where('is_available', true)
            ->whereHas('vehicle', fn ($q) => $q->where('vehicle_category_id', $vehicle->vehicle_category_id))
            ->get()
            ->filter(fn (Driver $driver) => $driver->hasFreshLocation() && ! $driver->activeBooking());

        foreach ($drivers as $driver) {
            $km = $this->drivingKm((float) $driver->current_lat, (float) $driver->current_lng, $pickupLat, $pickupLng);

            if ($km !== null && ($nearest === null || $km < $nearest)) {
                $nearest = $km;
            }
        }

        return $nearest;
    }

    private function drivingKm(float $fromLat, float $fromLng, float $toLat, float $toLng): ?float
    {
        $result = $this->maps->distance($fromLat, $fromLng, $toLat, $toLng);

        return isset($result['distance_km']) ? (float) $result['distance_km'] : null;
    }

    private function lockKey(Vehicle $vehicle, float $pickupLat, float $pickupLng): string
    {
        return sprintf('approach.%s.%s.%s', $vehicle->vehicle_category_id ?? 'none', round($pickupLat, 4), round($pickupLng, 4));
    }
}
