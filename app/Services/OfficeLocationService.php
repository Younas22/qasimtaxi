<?php

namespace App\Services;

use App\Models\Setting;

/**
 * Resolves the head office's coordinates for dispatch logic without ever
 * asking the admin to enter them — the office address lives in
 * Settings > General (the single source of truth for contact details). The
 * first time coordinates are needed, this geocodes that address once and
 * caches the result on the same Setting row, so it is never re-geocoded
 * unless the address changes.
 */
class OfficeLocationService
{
    public function __construct(private readonly GoogleMapsService $googleMaps) {}

    /**
     * @return array{lat: float, lng: float}|null
     */
    public function coordinates(): ?array
    {
        $settings = Setting::current();

        if ($settings->office_lat !== null && $settings->office_lng !== null) {
            return [
                'lat' => (float) $settings->office_lat,
                'lng' => (float) $settings->office_lng,
            ];
        }

        if (! $settings->address) {
            return null;
        }

        $geocoded = $this->googleMaps->geocode($settings->address);

        if (! $geocoded) {
            return null;
        }

        $settings->update([
            'office_lat' => $geocoded['lat'],
            'office_lng' => $geocoded['lng'],
        ]);

        return $geocoded;
    }
}
