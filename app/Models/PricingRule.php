<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class PricingRule extends Model
{
    public const WEEKDAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    protected $fillable = [
        'vehicle_category_id',
        'label',
        'base_fare',
        'base_fare_threshold_km',
        'km_fare',
        'mid_distance_threshold_km',
        'mid_distance_km_fare',
        'long_distance_threshold_km',
        'long_distance_km_fare',
        'very_long_distance_threshold_km',
        'very_long_distance_km_fare',
        'hour_fare',
        'waiting_charge_per_minute',
        'free_waiting_minutes',
        'night_charge',
        'night_start_time',
        'night_end_time',
        'weekend_charge',
        'weekend_days',
        'toll_charge',
        'airport_surcharge',
        'service_fee',
        'minimum_fare',
        'included_km',
        'included_hours',
        'included_passengers',
        'extra_passenger_charge',
        'approach_free_km',
        'approach_km_fare',
        'approach_driver_max_km',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'base_fare' => 'decimal:2',
            'base_fare_threshold_km' => 'decimal:2',
            'km_fare' => 'decimal:2',
            'mid_distance_threshold_km' => 'decimal:2',
            'mid_distance_km_fare' => 'decimal:2',
            'long_distance_threshold_km' => 'decimal:2',
            'long_distance_km_fare' => 'decimal:2',
            'very_long_distance_threshold_km' => 'decimal:2',
            'very_long_distance_km_fare' => 'decimal:2',
            'hour_fare' => 'decimal:2',
            'waiting_charge_per_minute' => 'decimal:2',
            'free_waiting_minutes' => 'integer',
            'night_charge' => 'decimal:2',
            'weekend_charge' => 'decimal:2',
            'weekend_days' => 'array',
            'toll_charge' => 'decimal:2',
            'airport_surcharge' => 'decimal:2',
            'service_fee' => 'decimal:2',
            'minimum_fare' => 'decimal:2',
            'included_km' => 'decimal:2',
            'included_hours' => 'decimal:2',
            'included_passengers' => 'integer',
            'extra_passenger_charge' => 'decimal:2',
            'approach_free_km' => 'decimal:2',
            'approach_km_fare' => 'decimal:2',
            'approach_driver_max_km' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(VehicleCategory::class, 'vehicle_category_id');
    }

    public static function global(): self
    {
        return static::firstOrCreate(
            ['vehicle_category_id' => null],
            [
                'label' => 'Global Default',
                'base_fare' => 25,
                'km_fare' => 2.3,
                'mid_distance_threshold_km' => 50,
                'mid_distance_km_fare' => 1.7,
                'long_distance_threshold_km' => 100,
                'long_distance_km_fare' => 1.5,
                'very_long_distance_threshold_km' => 200,
                'very_long_distance_km_fare' => 1.25,
                'hour_fare' => 40,
                'waiting_charge_per_minute' => 0.5,
                'free_waiting_minutes' => 10,
                'night_charge' => 15,
                'weekend_charge' => 10,
                'weekend_days' => ['Saturday', 'Sunday'],
                'toll_charge' => 8,
                'airport_surcharge' => 20,
                'service_fee' => 5,
                'minimum_fare' => 0,
                'included_km' => 0,
                'included_hours' => 0,
                'included_passengers' => 4,
                'extra_passenger_charge' => 0,
            ]
        );
    }

    public static function resolveForVehicle(Vehicle $vehicle): self
    {
        $rule = $vehicle->vehicle_category_id
            ? static::where('vehicle_category_id', $vehicle->vehicle_category_id)->where('is_active', true)->first()
            : null;

        return $rule ?? static::global();
    }

    /**
     * Whether the base fare applies to a trip of this total distance — when
     * a threshold is set, only short trips (at or under it) get the base
     * fare; longer trips bill purely on distance instead. With no threshold
     * configured, the base fare always applies (see isWithinFlatFareTier()
     * for the other half of that "no threshold" case).
     */
    public function appliesBaseFare(float $totalDistanceKm): bool
    {
        return $this->base_fare_threshold_km === null || $totalDistanceKm <= (float) $this->base_fare_threshold_km;
    }

    /**
     * Whether this trip falls into the flat "base fare only, no per-km
     * charge" tier — true only when a threshold is actually configured AND
     * the trip is at or under it.
     *
     * This is deliberately NOT the same check as appliesBaseFare() above.
     * A rule with no threshold set (base_fare + km_fare configured together,
     * as most vehicle categories are) means plain "base fare + per-km for
     * the whole trip" pricing — the base fare always applies, but so should
     * the distance charge. Reusing appliesBaseFare()'s "no threshold ->
     * true" default to also gate the distance charge (as this codebase used
     * to) made every such rule charge the flat base fare only, forever,
     * regardless of distance — a 100 km trip and a 5 km trip billed
     * identically. See BookingFareCalculator::breakdown().
     */
    public function isWithinFlatFareTier(float $totalDistanceKm): bool
    {
        return $this->base_fare_threshold_km !== null && $totalDistanceKm <= (float) $this->base_fare_threshold_km;
    }

    /**
     * The per-km rate to bill for a trip of this total distance — the whole
     * trip bills at long_distance_km_fare once the threshold is crossed,
     * rather than blending both rates across the trip.
     */
    public function effectiveKmFare(float $totalDistanceKm): float
    {
        $distanceKm = max((float) $totalDistanceKm, 0);

        if (
            $this->very_long_distance_threshold_km !== null
            && $this->very_long_distance_km_fare !== null
            && $distanceKm >= (float) $this->very_long_distance_threshold_km
        ) {
            return (float) $this->very_long_distance_km_fare;
        }

        if (
            $this->long_distance_threshold_km !== null
            && $this->long_distance_km_fare !== null
            && $distanceKm >= (float) $this->long_distance_threshold_km
        ) {
            return (float) $this->long_distance_km_fare;
        }

        if (
            $this->mid_distance_threshold_km !== null
            && $this->mid_distance_km_fare !== null
            && $distanceKm >= (float) $this->mid_distance_threshold_km
        ) {
            return (float) $this->mid_distance_km_fare;
        }

        return (float) $this->km_fare;
    }

    public function distanceFare(float $totalDistanceKm): float
    {
        $distanceKm = max((float) $totalDistanceKm, 0);
        $fare = 0.0;

        $firstBandKm = min($distanceKm, (float) ($this->mid_distance_threshold_km ?? 50));
        $fare += $firstBandKm * (float) $this->km_fare;
        $remainingKm = max($distanceKm - $firstBandKm, 0);

        $secondBandThreshold = $this->long_distance_threshold_km ?? ($this->mid_distance_threshold_km !== null ? (float) $this->mid_distance_threshold_km + 50 : 100);
        $secondBandKm = min($remainingKm, max($secondBandThreshold - ($this->mid_distance_threshold_km ?? 50), 0));
        $fare += $secondBandKm * (float) ($this->mid_distance_km_fare ?? $this->km_fare);
        $remainingKm = max($remainingKm - $secondBandKm, 0);

        $thirdBandThreshold = $this->very_long_distance_threshold_km ?? ($this->long_distance_threshold_km ?? 200);
        $thirdBandKm = min($remainingKm, max($thirdBandThreshold - ($this->long_distance_threshold_km ?? ($this->mid_distance_threshold_km ?? 50)), 0));
        $fare += $thirdBandKm * (float) ($this->long_distance_km_fare ?? $this->mid_distance_km_fare ?? $this->km_fare);
        $remainingKm = max($remainingKm - $thirdBandKm, 0);

        $fare += $remainingKm * (float) ($this->very_long_distance_km_fare ?? $this->long_distance_km_fare ?? $this->mid_distance_km_fare ?? $this->km_fare);

        return round($fare, 2);
    }

    /**
     * Charge for the driver's empty run to the pickup point (see
     * ApproachDistanceService for where that run starts from): the
     * first approach_free_km are absorbed by the driver, every km beyond
     * that bills at approach_km_fare. E.g. free 5 km, pickup 10 km away ->
     * 5 km charged. A zero rate turns the feature off.
     */
    public function approachCharge(?float $approachDistanceKm): float
    {
        if ($approachDistanceKm === null || (float) $this->approach_km_fare <= 0) {
            return 0.0;
        }

        $chargeableKm = max($approachDistanceKm - (float) $this->approach_free_km, 0);

        return round($chargeableKm * (float) $this->approach_km_fare, 2);
    }

    public function isNight(Carbon $pickupDateTime): bool
    {
        $time = $pickupDateTime->format('H:i:s');
        $start = $this->night_start_time instanceof Carbon ? $this->night_start_time->format('H:i:s') : (string) $this->night_start_time;
        $end = $this->night_end_time instanceof Carbon ? $this->night_end_time->format('H:i:s') : (string) $this->night_end_time;

        if ($start === $end) {
            return false;
        }

        // Overnight window (e.g. 22:00 - 06:00) wraps past midnight.
        if ($start > $end) {
            return $time >= $start || $time < $end;
        }

        return $time >= $start && $time < $end;
    }

    public function isWeekend(Carbon $pickupDateTime): bool
    {
        return in_array($pickupDateTime->format('l'), $this->weekend_days ?? [], true);
    }
}
