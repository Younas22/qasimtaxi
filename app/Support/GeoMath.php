<?php

namespace App\Support;

/**
 * Shared great-circle distance helper — used wherever we need to decide
 * "has this point moved enough to matter" without calling out to Google
 * (DriverLocationService for GPS write-throttling, DriverDispatchService
 * for cached-dispatch-snapshot staleness checks).
 */
class GeoMath
{
    public static function metersBetween(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusMeters = 6371000;

        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);

        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lngDelta / 2) ** 2;

        return $earthRadiusMeters * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
