<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingSetting;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\QuoteRequest;
use App\Models\Vehicle;
use App\Services\ApproachDistanceService;
use App\Services\BookingCreationService;
use App\Services\BookingFareCalculator;
use App\Services\GoogleMapsService;
use App\Services\OfficeLocationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * "Website booking" mode of the public booking widget — a lightweight guest
 * checkout that creates a real pending Booking (the same one admins manage),
 * as the website-native alternative to the WhatsApp booking mode.
 */
class BookingRequestController extends Controller
{
    public function store(Request $request, BookingCreationService $bookingCreation, ApproachDistanceService $approachDistance): RedirectResponse
    {
        $settings = BookingSetting::current();

        if (! $settings->website_booking_enabled || ! $settings->guest_booking_enabled) {
            return back()->withInput()->with('error', 'Website booking is currently unavailable. Please contact us via WhatsApp instead.');
        }

        $loggedInCustomer = auth()->guard('customer')->user();

        $data = $request->validate([
            'name' => [Rule::requiredIf(! $loggedInCustomer), 'nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'vehicle_category_id' => ['required', 'exists:vehicle_categories,id'],
            'type' => ['nullable', Rule::in(array_keys(Booking::TYPES))],
            'pickup_location' => ['required', 'string', 'max:255'],
            'pickup_lat' => ['nullable', 'numeric'],
            'pickup_lng' => ['nullable', 'numeric'],
            'pickup_place_id' => ['nullable', 'string', 'max:255'],
            'dropoff_location' => ['nullable', 'required_unless:type,hourly', 'string', 'max:255'],
            'dropoff_lat' => ['nullable', 'numeric'],
            'dropoff_lng' => ['nullable', 'numeric'],
            'dropoff_place_id' => ['nullable', 'string', 'max:255'],
            'stops' => ['nullable', 'array'],
            'stops.*.location' => ['nullable', 'string', 'max:255'],
            'stops.*.lat' => ['nullable', 'numeric'],
            'stops.*.lng' => ['nullable', 'numeric'],
            'stops.*.place_id' => ['nullable', 'string', 'max:255'],
            'pickup_date' => ['required', 'date', 'after_or_equal:today'],
            'pickup_time' => ['required', 'date_format:H:i'],
            'return_date' => ['nullable', 'required_if:type,round_trip', 'date', 'after_or_equal:pickup_date'],
            'return_time' => ['nullable', 'required_if:type,round_trip', 'date_format:H:i'],
            'return_pickup_location' => ['nullable', 'required_if:type,round_trip', 'string', 'max:255'],
            'return_pickup_lat' => ['nullable', 'numeric'],
            'return_pickup_lng' => ['nullable', 'numeric'],
            'return_pickup_place_id' => ['nullable', 'string', 'max:255'],
            'return_dropoff_location' => ['nullable', 'required_if:type,round_trip', 'string', 'max:255'],
            'return_dropoff_lat' => ['nullable', 'numeric'],
            'return_dropoff_lng' => ['nullable', 'numeric'],
            'return_dropoff_place_id' => ['nullable', 'string', 'max:255'],
            'return_stops' => ['nullable', 'array'],
            'return_stops.*.location' => ['nullable', 'string', 'max:255'],
            'return_stops.*.lat' => ['nullable', 'numeric'],
            'return_stops.*.lng' => ['nullable', 'numeric'],
            'return_stops.*.place_id' => ['nullable', 'string', 'max:255'],
            'return_distance_km' => ['nullable', 'numeric', 'min:0'],
            'hours' => ['nullable', 'integer', 'min:1', 'max:24'],
            'distance_km' => ['nullable', 'numeric', 'min:0'],
            'passengers' => ['required', 'integer', 'min:1', 'max:20'],
            'luggage' => ['required', 'integer', 'min:0', 'max:20'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
        ]);

        $vehicle = Vehicle::where('vehicle_category_id', $data['vehicle_category_id'])
            ->where('status', true)
            ->inRandomOrder()
            ->first();

        if (! $vehicle) {
            return back()->withInput()->with('error', 'That vehicle category is not currently available. Please choose another, or contact us via WhatsApp.');
        }

        // With no email, there's no reliable identity to dedupe guests by —
        // firstOrCreate(['email' => null], ...) would incorrectly reuse
        // whichever earlier emailless guest happens to match first, so
        // every emailless booking gets its own fresh Customer record.
        $customer = $loggedInCustomer
            ?? (! empty($data['email'])
                ? Customer::firstOrCreate(
                    ['email' => $data['email']],
                    ['name' => $data['name'], 'phone' => $data['phone'] ?? null, 'status' => true]
                )
                : Customer::create([
                    'name' => $data['name'],
                    'phone' => $data['phone'] ?? null,
                    'status' => true,
                ]));

        $bookingData = [
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'driver_id' => null,
            'type' => $data['type'] ?? 'one_way',
            'pickup_location' => $data['pickup_location'],
            'pickup_lat' => $data['pickup_lat'] ?? null,
            'pickup_lng' => $data['pickup_lng'] ?? null,
            'pickup_place_id' => $data['pickup_place_id'] ?? null,
            'dropoff_location' => $data['dropoff_location'] ?? '',
            'dropoff_lat' => $data['dropoff_lat'] ?? null,
            'dropoff_lng' => $data['dropoff_lng'] ?? null,
            'dropoff_place_id' => $data['dropoff_place_id'] ?? null,
            'stops' => $this->normalizeStops($data['stops'] ?? []),
            'pickup_datetime' => Carbon::parse($data['pickup_date'].' '.$data['pickup_time']),
            'return_datetime' => ($data['return_date'] ?? null) && ($data['return_time'] ?? null)
                ? Carbon::parse($data['return_date'].' '.$data['return_time'])
                : null,
            'return_pickup_location' => $data['return_pickup_location'] ?? null,
            'return_pickup_lat' => $data['return_pickup_lat'] ?? null,
            'return_pickup_lng' => $data['return_pickup_lng'] ?? null,
            'return_pickup_place_id' => $data['return_pickup_place_id'] ?? null,
            'return_dropoff_location' => $data['return_dropoff_location'] ?? null,
            'return_dropoff_lat' => $data['return_dropoff_lat'] ?? null,
            'return_dropoff_lng' => $data['return_dropoff_lng'] ?? null,
            'return_dropoff_place_id' => $data['return_dropoff_place_id'] ?? null,
            'return_stops' => $this->normalizeStops($data['return_stops'] ?? []),
            'return_distance_km' => $data['return_distance_km'] ?? null,
            'hours' => $data['hours'] ?? null,
            'distance_km' => $data['distance_km'] ?? null,
            'approach_distance_km' => $approachDistance->forBooking($vehicle, $data['pickup_lat'] ?? null, $data['pickup_lng'] ?? null, isset($data['distance_km']) ? (float) $data['distance_km'] : null),
            'passengers' => $data['passengers'],
            'luggage' => $data['luggage'],
            'waiting_minutes' => 0,
            'has_toll' => false,
            'notes' => null,
            'status' => 'pending',
            'payment_status' => 'pending',
        ];

        $bookingData = $bookingCreation->attachFareBreakdown($bookingData);
        unset($bookingData['approach_distance_km']);
        [$bookingData, $appliedCoupon] = $bookingCreation->applyCoupon($bookingData, $data['coupon_code'] ?? null);
        $bookingData = $bookingCreation->applyPolicies($bookingData);

        $booking = Booking::create($bookingData);

        // Only counted once the booking this coupon discounted has actually
        // been created — never on a quote preview, and never twice for the
        // same booking.
        $appliedCoupon?->increment('used_count');

        $bookingCreation->notifyAdminsOfCreation($booking);
        $bookingCreation->notifyCustomerOfCreation($booking);
        $bookingCreation->notifyDriverOfAssignment($booking);

        return redirect()->route('booking.confirmation', $booking->booking_number);
    }

    /**
     * Live AJAX quote for the public booking widget: resolves distance via
     * Google Distance Matrix (skipped for hourly bookings) and runs the same
     * fare engine used for real bookings, so the preview never drifts from
     * what the customer is actually charged.
     */
    public function quote(Request $request, GoogleMapsService $maps, BookingFareCalculator $calculator, OfficeLocationService $officeLocation, ApproachDistanceService $approachDistance): JsonResponse
    {
        $data = $request->validate([
            'vehicle_category_id' => ['required', 'exists:vehicle_categories,id'],
            'type' => ['required', Rule::in(array_keys(Booking::TYPES))],
            'pickup_location' => ['nullable', 'string', 'max:255'],
            'pickup_lat' => ['nullable', 'numeric'],
            'pickup_lng' => ['nullable', 'numeric'],
            'pickup_place_id' => ['nullable', 'string', 'max:255'],
            'dropoff_location' => ['nullable', 'string', 'max:255'],
            'dropoff_lat' => ['nullable', 'numeric'],
            'dropoff_lng' => ['nullable', 'numeric'],
            'dropoff_place_id' => ['nullable', 'string', 'max:255'],
            'stops' => ['nullable', 'array'],
            'stops.*.location' => ['nullable', 'string', 'max:255'],
            'stops.*.lat' => ['nullable', 'numeric'],
            'stops.*.lng' => ['nullable', 'numeric'],
            'return_pickup_location' => ['nullable', 'string', 'max:255'],
            'return_pickup_lat' => ['nullable', 'numeric'],
            'return_pickup_lng' => ['nullable', 'numeric'],
            'return_dropoff_location' => ['nullable', 'string', 'max:255'],
            'return_dropoff_lat' => ['nullable', 'numeric'],
            'return_dropoff_lng' => ['nullable', 'numeric'],
            'return_stops' => ['nullable', 'array'],
            'return_stops.*.location' => ['nullable', 'string', 'max:255'],
            'return_stops.*.lat' => ['nullable', 'numeric'],
            'return_stops.*.lng' => ['nullable', 'numeric'],
            'hours' => ['nullable', 'integer', 'min:1', 'max:24'],
            'passengers' => ['nullable', 'integer', 'min:1', 'max:60'],
            'pickup_date' => ['nullable', 'date'],
            'pickup_time' => ['nullable', 'date_format:H:i'],
        ]);

        $vehicle = Vehicle::where('vehicle_category_id', $data['vehicle_category_id'])
            ->where('status', true)
            ->first();

        if (! $vehicle) {
            return response()->json(['message' => 'No vehicles are currently available in that category.'], 422);
        }

        $distanceKm = null;
        $durationMinutes = null;

        $needsDistance = $data['type'] !== 'hourly'
            && isset($data['pickup_lat'], $data['pickup_lng'], $data['dropoff_lat'], $data['dropoff_lng']);

        if ($needsDistance) {
            $waypoints = [
                ['lat' => (float) $data['pickup_lat'], 'lng' => (float) $data['pickup_lng']],
                ...$this->stopWaypoints($data['stops'] ?? []),
                ['lat' => (float) $data['dropoff_lat'], 'lng' => (float) $data['dropoff_lng']],
            ];

            $result = $maps->routeDistance($waypoints);

            if (! $result) {
                return response()->json(['message' => 'Unable to calculate distance.'], 422);
            }

            $distanceKm = $result['distance_km'];
            $durationMinutes = $result['duration_minutes'];
        }

        $returnDistanceKm = null;
        $returnDurationMinutes = null;

        $needsReturnDistance = $data['type'] === 'round_trip'
            && isset($data['return_pickup_lat'], $data['return_pickup_lng'], $data['return_dropoff_lat'], $data['return_dropoff_lng']);

        if ($needsReturnDistance) {
            $returnWaypoints = [
                ['lat' => (float) $data['return_pickup_lat'], 'lng' => (float) $data['return_pickup_lng']],
                ...$this->stopWaypoints($data['return_stops'] ?? []),
                ['lat' => (float) $data['return_dropoff_lat'], 'lng' => (float) $data['return_dropoff_lng']],
            ];

            $returnResult = $maps->routeDistance($returnWaypoints);

            // The return leg is optional context for a better quote — if it
            // fails, fall back to the outbound-leg estimate rather than
            // failing the whole quote (the calculator already does this via
            // the null $returnDistanceKm fallback).
            if ($returnResult) {
                $returnDistanceKm = $returnResult['distance_km'];
                $returnDurationMinutes = $returnResult['duration_minutes'];
            }
        }

        $pickupDateTime = ($data['pickup_date'] ?? null) && ($data['pickup_time'] ?? null)
            ? Carbon::parse($data['pickup_date'].' '.$data['pickup_time'])
            : now();

        $breakdown = $calculator->breakdown(
            $vehicle,
            $data['type'],
            $distanceKm,
            $data['hours'] ?? null,
            $pickupDateTime,
            0,
            false,
            (int) ($data['passengers'] ?? 1),
            $returnDistanceKm,
            $approachDistance->forQuote($vehicle, $data['pickup_lat'] ?? null, $data['pickup_lng'] ?? null, $distanceKm)
        );

        try {
            QuoteRequest::create([
                'pickup_location' => $data['pickup_location'] ?? null,
                'pickup_lat' => $data['pickup_lat'] ?? null,
                'pickup_lng' => $data['pickup_lng'] ?? null,
                'pickup_place_id' => $data['pickup_place_id'] ?? null,
                'dropoff_location' => $data['dropoff_location'] ?? null,
                'dropoff_lat' => $data['dropoff_lat'] ?? null,
                'dropoff_lng' => $data['dropoff_lng'] ?? null,
                'dropoff_place_id' => $data['dropoff_place_id'] ?? null,
                'vehicle_category_id' => $data['vehicle_category_id'],
                'type' => $data['type'],
                'distance_km' => $distanceKm,
                'duration_minutes' => $durationMinutes,
                'return_pickup_location' => $data['return_pickup_location'] ?? null,
                'return_pickup_lat' => $data['return_pickup_lat'] ?? null,
                'return_pickup_lng' => $data['return_pickup_lng'] ?? null,
                'return_dropoff_location' => $data['return_dropoff_location'] ?? null,
                'return_dropoff_lat' => $data['return_dropoff_lat'] ?? null,
                'return_dropoff_lng' => $data['return_dropoff_lng'] ?? null,
                'return_distance_km' => $returnDistanceKm,
                'return_duration_minutes' => $returnDurationMinutes,
                'hours' => $data['hours'] ?? null,
                'passengers' => $data['passengers'] ?? null,
                'fare_breakdown' => $breakdown,
                'total' => $breakdown['total'],
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to log quote request: '.$e->getMessage());
        }

        $office = $officeLocation->coordinates();
        $hasPickupCoords = isset($data['pickup_lat'], $data['pickup_lng']);

        $driversInfo = Driver::active()
            ->where('is_online', true)
            ->whereHas('vehicle', fn ($q) => $q->where('vehicle_category_id', $data['vehicle_category_id']))
            ->get()
            ->map(function (Driver $driver) use ($maps, $office, $data, $hasPickupCoords, $pickupDateTime) {
                $activeRide = $driver->activeBooking();

                // Busy drivers are still physically somewhere — if we have a
                // live GPS ping for them, show how far they are right now
                // just like an available driver. Busy drivers don't fall
                // back to the office (they're out on a ride, not parked
                // there), so no location simply means no distance shown.
                $origin = $driver->hasFreshLocation()
                    ? ['lat' => (float) $driver->current_lat, 'lng' => (float) $driver->current_lng]
                    : ($activeRide ? null : $office);

                $distance = ($hasPickupCoords && $origin)
                    ? $maps->distance($origin['lat'], $origin['lng'], (float) $data['pickup_lat'], (float) $data['pickup_lng'])
                    : null;

                if ($activeRide) {
                    // ride_ends_in_minutes floors at 0 and stays there once
                    // the estimate has elapsed but the driver hasn't pressed
                    // "Complete Ride" — surface that distinction so the UI
                    // doesn't misleadingly say "free in 0 min" forever.
                    $freeInMinutes = $activeRide->ride_ends_in_minutes;

                    return [
                        'name' => Str::before($driver->name, ' '),
                        'status' => 'busy',
                        'free_in_minutes' => $freeInMinutes,
                        'overdue' => $freeInMinutes === 0 && $activeRide->estimated_arrival_at?->isPast(),
                        // Free before the requested pickup time, so this
                        // driver can still take the booking on time — just
                        // not dispatchable from where they are RIGHT now.
                        'free_before_pickup' => $activeRide->estimated_arrival_at?->lt($pickupDateTime) ?? false,
                        'distance_km' => $distance['distance_km'] ?? null,
                        'duration_minutes' => $distance['duration_minutes'] ?? null,
                    ];
                }

                return [
                    'name' => Str::before($driver->name, ' '),
                    'status' => 'available',
                    'free_in_minutes' => null,
                    'overdue' => false,
                    'free_before_pickup' => true,
                    'distance_km' => $distance['distance_km'] ?? null,
                    'duration_minutes' => $distance['duration_minutes'] ?? null,
                ];
            })
            ->sortBy(fn (array $d) => $d['status'] === 'available' ? 0 : 1)
            ->values()
            ->all();

        $availableDriversCount = collect($driversInfo)->where('free_before_pickup', true)->count();

        return response()->json([
            'distance_km' => $distanceKm,
            'duration_minutes' => $durationMinutes,
            'return_distance_km' => $returnDistanceKm,
            'return_duration_minutes' => $returnDurationMinutes,
            'vehicle_name' => $vehicle->category?->name ?? $vehicle->name,
            'available_drivers_count' => $availableDriversCount,
            'drivers' => $driversInfo,
            'breakdown' => $breakdown,
        ]);
    }

    public function confirmation(string $bookingNumber): View
    {
        $booking = Booking::with(['vehicle.category', 'coupon'])->where('booking_number', $bookingNumber)->firstOrFail();

        return view('booking.confirmation', compact('booking'));
    }

    public function invoice(string $bookingNumber): View
    {
        $booking = Booking::with(['vehicle.category', 'driver', 'customer', 'coupon'])->where('booking_number', $bookingNumber)->firstOrFail();

        return view('booking.invoice', compact('booking'));
    }

    public function downloadInvoice(string $bookingNumber)
    {
        $booking = Booking::with(['vehicle.category', 'driver', 'customer', 'coupon'])->where('booking_number', $bookingNumber)->firstOrFail();

        $logoPath = setting('invoice_logo_path');

        $filename = ($booking->payment_status === 'paid' ? 'receipt-' : 'invoice-').$booking->invoice_number.'.pdf';

        return Pdf::loadView('booking.invoice-pdf', compact('booking', 'logoPath'))
            ->setPaper('a4')
            ->download($filename);
    }

    /**
     * Turns submitted stops into ordered {lat, lng} waypoints for
     * GoogleMapsService::routeDistance() — only stops the user actually
     * picked from Autocomplete (and therefore have coordinates) can
     * contribute to the route distance; free-typed-only stops are skipped
     * rather than failing the whole quote.
     *
     * @param  array<int, array{location?: string, lat?: mixed, lng?: mixed}>  $stops
     * @return array<int, array{lat: float, lng: float}>
     */
    private function stopWaypoints(array $stops): array
    {
        return collect($stops)
            ->filter(fn ($stop) => is_array($stop) && isset($stop['lat'], $stop['lng']) && $stop['lat'] !== '' && $stop['lng'] !== '')
            ->map(fn ($stop) => ['lat' => (float) $stop['lat'], 'lng' => (float) $stop['lng']])
            ->values()
            ->all();
    }

    /**
     * Normalizes submitted stops into the {location, lat, lng, place_id}
     * shape stored on the booking, dropping any row without a location.
     *
     * @param  array<int, array{location?: string, lat?: mixed, lng?: mixed, place_id?: string}>  $stops
     * @return array<int, array{location: string, lat: ?float, lng: ?float, place_id: ?string}>
     */
    private function normalizeStops(array $stops): array
    {
        return collect($stops)
            ->filter(fn ($stop) => is_array($stop) && filled($stop['location'] ?? null))
            ->map(fn ($stop) => [
                'location' => $stop['location'],
                'lat' => filled($stop['lat'] ?? null) ? (float) $stop['lat'] : null,
                'lng' => filled($stop['lng'] ?? null) ? (float) $stop['lng'] : null,
                'place_id' => $stop['place_id'] ?? null,
            ])
            ->values()
            ->all();
    }
}
