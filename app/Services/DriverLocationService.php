<?php

namespace App\Services;

use App\Models\Driver;
use App\Support\GeoMath;

/**
 * Records a driver's GPS ping, but only writes to the database when the
 * ping is actually worth recording — a chatty client (watchPosition can
 * fire very frequently) shouldn't turn into a write on every tick.
 */
class DriverLocationService
{
    private const MIN_SECONDS_BETWEEN_WRITES = 8;

    private const MIN_METERS_BETWEEN_WRITES = 50;

    public function report(Driver $driver, float $lat, float $lng): void
    {
        if (! $this->isWorthRecording($driver, $lat, $lng)) {
            return;
        }

        $driver->update([
            'current_lat' => $lat,
            'current_lng' => $lng,
            'location_updated_at' => now(),
        ]);
    }

    private function isWorthRecording(Driver $driver, float $lat, float $lng): bool
    {
        if ($driver->current_lat === null || $driver->location_updated_at === null) {
            return true;
        }

        if ($driver->location_updated_at->lte(now()->subSeconds(self::MIN_SECONDS_BETWEEN_WRITES))) {
            return true;
        }

        return GeoMath::metersBetween(
            (float) $driver->current_lat,
            (float) $driver->current_lng,
            $lat,
            $lng
        ) >= self::MIN_METERS_BETWEEN_WRITES;
    }
}
