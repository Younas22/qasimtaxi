<?php

namespace App\Services;

use App\Models\PricingRule;
use App\Models\Vehicle;
use Carbon\Carbon;

/**
 * Dynamic pricing engine: resolves the vehicle's category pricing rule
 * (falling back to the global default) and builds a full fare breakdown
 * from it. The result is a suggestion — the admin can always override the
 * final fare_amount by hand.
 */
class BookingFareCalculator
{
    public function calculate(
        Vehicle $vehicle,
        string $type,
        ?float $distanceKm,
        ?int $hours,
        Carbon $pickupDateTime,
        int $waitingMinutes = 0,
        bool $hasToll = false,
        int $passengers = 1,
        ?float $returnDistanceKm = null,
        ?float $approachDistanceKm = null
    ): float {
        return $this->breakdown($vehicle, $type, $distanceKm, $hours, $pickupDateTime, $waitingMinutes, $hasToll, $passengers, $returnDistanceKm, $approachDistanceKm)['total'];
    }

    /**
     * @return array<string, float>
     */
    public function breakdown(
        Vehicle $vehicle,
        string $type,
        ?float $distanceKm,
        ?int $hours,
        Carbon $pickupDateTime,
        int $waitingMinutes = 0,
        bool $hasToll = false,
        int $passengers = 1,
        ?float $returnDistanceKm = null,
        ?float $approachDistanceKm = null
    ): array {
        $rule = PricingRule::resolveForVehicle($vehicle);
        $distanceKm = $distanceKm ?? 0;
        $isRoundTrip = $type === 'round_trip';
        $legMultiplier = $isRoundTrip ? 2 : 1;

        // Round trip: sum both legs' real distances when the return leg's
        // distance is known (independent return route); fall back to
        // doubling the outbound leg when it isn't (e.g. hourly/no return
        // route captured yet).
        $totalDistance = $isRoundTrip
            ? $distanceKm + ($returnDistanceKm ?? $distanceKm)
            : $distanceKm;

        // Within the base-fare threshold (e.g. 6 km), the flat base fare
        // *is* the fare — no per-km charge on top. Beyond it, there's no
        // base fare at all and the trip bills purely on distance.
        $appliesBaseFare = $rule->appliesBaseFare($totalDistance);

        $baseFare = $appliesBaseFare
            ? round((float) $rule->base_fare * $legMultiplier, 2)
            : 0.0;

        $billableKm = max($totalDistance - (float) $rule->included_km, 0);
        $distanceFare = ($type === 'hourly' || $rule->isWithinFlatFareTier($totalDistance))
            ? 0.0
            : round($rule->distanceFare($billableKm), 2);

        $billableHours = max(max($hours ?? 1, 1) - (float) $rule->included_hours, 0);
        $hourFare = $type === 'hourly'
            ? round((float) $rule->hour_fare * $billableHours, 2)
            : 0.0;

        $waitingChargeableMinutes = max($waitingMinutes - $rule->free_waiting_minutes, 0);
        $waitingCharge = round((float) $rule->waiting_charge_per_minute * $waitingChargeableMinutes, 2);

        $nightCharge = $rule->isNight($pickupDateTime) ? round((float) $rule->night_charge, 2) : 0.0;
        $weekendCharge = $rule->isWeekend($pickupDateTime) ? round((float) $rule->weekend_charge, 2) : 0.0;
        $tollCharge = $hasToll ? round((float) $rule->toll_charge, 2) : 0.0;
        $airportSurcharge = $type === 'airport_transfer' ? round((float) $rule->airport_surcharge, 2) : 0.0;
        $serviceFee = round((float) $rule->service_fee, 2);

        $extraPassengers = max($passengers - (int) $rule->included_passengers, 0);
        $extraPassengerCharge = round((float) $rule->extra_passenger_charge * $extraPassengers, 2);

        // Driver's run from base to the pickup — charged once, even on a
        // round trip, and only for the km beyond the rule's free allowance.
        $approachCharge = $rule->approachCharge($approachDistanceKm);

        $total = round(
            $baseFare + $distanceFare + $hourFare + $waitingCharge
            + $nightCharge + $weekendCharge + $tollCharge + $airportSurcharge + $serviceFee + $extraPassengerCharge + $approachCharge,
            2
        );

        if ((float) $rule->minimum_fare > 0) {
            $total = max($total, round((float) $rule->minimum_fare, 2));
        }

        return [
            'base_fare' => $baseFare,
            'distance_fare' => $distanceFare,
            'hour_fare' => $hourFare,
            'waiting_charge' => $waitingCharge,
            'night_charge' => $nightCharge,
            'weekend_charge' => $weekendCharge,
            'toll_charge' => $tollCharge,
            'airport_surcharge' => $airportSurcharge,
            'service_fee' => $serviceFee,
            'extra_passenger_charge' => $extraPassengerCharge,
            'approach_charge' => $approachCharge,
            'total' => $total,
        ];
    }
}
