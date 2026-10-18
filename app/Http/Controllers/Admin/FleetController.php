<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Services\DriverDispatchService;
use App\Services\GoogleMapsService;
use App\Services\OfficeLocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class FleetController extends Controller
{
    public function __construct(
        private readonly DriverDispatchService $dispatchService,
        private readonly GoogleMapsService $googleMaps,
        private readonly OfficeLocationService $officeLocation,
    ) {}

    public function index(): View
    {
        return view('admin.fleet.index', [
            'rows' => $this->buildRows(),
            'office' => $this->officeLocation->coordinates(),
        ]);
    }

    public function data(): JsonResponse
    {
        return response()->json([
            'rows' => $this->buildRows(),
            'office' => $this->officeLocation->coordinates(),
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildRows(): array
    {
        $office = $this->officeLocation->coordinates();

        return Driver::active()
            ->with(['vehicle.category'])
            ->orderByDesc('is_online')
            ->orderBy('name')
            ->get()
            ->map(function (Driver $driver) use ($office) {
                $vehicleCategory = $driver->vehicle?->category?->name;

                // Skip Google/dispatch lookups entirely for offline drivers —
                // there's nothing dispatchable to compute and it would just
                // burn API calls on every poll for drivers who aren't working.
                if (! $driver->is_online) {
                    return [
                        'id' => $driver->id,
                        'name' => $driver->name,
                        'vehicle_category' => $vehicleCategory,
                        'online' => false,
                        'status' => 'offline',
                        'location' => __('Offline'),
                        'lat' => null,
                        'lng' => null,
                        'current_ride' => null,
                        'ride_ends_in_minutes' => null,
                        'distance_from_office_km' => null,
                        'next_pickup' => null,
                        'distance_to_pickup_km' => null,
                        'eta_minutes' => null,
                    ];
                }

                $activeRide = $driver->activeBooking();
                $hasFreshLocation = $driver->hasFreshLocation();

                $distanceFromOffice = ($hasFreshLocation && $office)
                    ? $this->googleMaps->distance((float) $driver->current_lat, (float) $driver->current_lng, $office['lat'], $office['lng'])
                    : null;

                $upcoming = $activeRide ? null : $driver->bookings()
                    ->where('status', 'assigned')
                    ->where('pickup_datetime', '>=', now())
                    ->orderBy('pickup_datetime')
                    ->first();

                $upcomingDispatch = $upcoming ? $this->dispatchService->dispatchInfoFor($upcoming) : null;

                return [
                    'id' => $driver->id,
                    'name' => $driver->name,
                    'vehicle_category' => $vehicleCategory,
                    'online' => true,
                    'status' => $activeRide ? 'busy' : 'available',
                    'location' => $hasFreshLocation
                        ? round((float) $driver->current_lat, 4).', '.round((float) $driver->current_lng, 4)
                        : __('Head Office'),
                    'lat' => $hasFreshLocation ? (float) $driver->current_lat : ($office['lat'] ?? null),
                    'lng' => $hasFreshLocation ? (float) $driver->current_lng : ($office['lng'] ?? null),
                    'current_ride' => $activeRide
                        ? Str::limit($activeRide->pickup_location, 20).' → '.Str::limit($activeRide->dropoff_location, 20)
                        : null,
                    'ride_ends_in_minutes' => $activeRide?->ride_ends_in_minutes,
                    'distance_from_office_km' => $distanceFromOffice['distance_km'] ?? null,
                    'next_pickup' => $upcoming ? Str::limit($upcoming->pickup_location, 24) : null,
                    'distance_to_pickup_km' => $upcomingDispatch['distance_km'] ?? null,
                    'eta_minutes' => $upcomingDispatch['duration_minutes'] ?? null,
                ];
            })
            ->values()
            ->all();
    }
}
