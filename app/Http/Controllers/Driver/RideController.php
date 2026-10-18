<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\GoogleMapsService;
use App\Services\PushNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class RideController extends Controller
{
    public function start(Booking $booking, GoogleMapsService $googleMaps, PushNotificationService $pushNotifications): RedirectResponse
    {
        $driver = Auth::guard('driver')->user();

        abort_unless($booking->driver_id === $driver->id, 404);

        if ($booking->status !== 'assigned') {
            return back()->with('error', __('This ride cannot be started right now.'));
        }

        $estimatedArrivalAt = now()->addMinutes(30);

        if ($booking->pickup_lat && $booking->pickup_lng && $booking->dropoff_lat && $booking->dropoff_lng) {
            $eta = $googleMaps->distance(
                (float) $booking->pickup_lat,
                (float) $booking->pickup_lng,
                (float) $booking->dropoff_lat,
                (float) $booking->dropoff_lng
            );

            if ($eta) {
                $estimatedArrivalAt = now()->addMinutes($eta['duration_minutes']);
            }
        }

        $booking->update([
            'status' => 'in_progress',
            'ride_started_at' => now(),
            'estimated_arrival_at' => $estimatedArrivalAt,
        ]);

        $driver->update(['is_available' => false]);

        if ($booking->customer) {
            $pushNotifications->send(
                $booking->customer,
                'trip_started',
                __('Trip Started'),
                __('Your trip for booking #:number has started.', ['number' => $booking->booking_number]),
                route('customer.bookings.show', $booking),
                $booking->id,
                ['booking_number' => $booking->booking_number]
            );
        }

        return back()->with('status', __('Ride started.'));
    }

    public function complete(Booking $booking, PushNotificationService $pushNotifications): RedirectResponse
    {
        $driver = Auth::guard('driver')->user();

        abort_unless($booking->driver_id === $driver->id, 404);

        if ($booking->status !== 'in_progress') {
            return back()->with('error', __('This ride cannot be completed right now.'));
        }

        $booking->update(['status' => 'completed']);

        // The driver's current GPS position (already tracked live) becomes
        // the new dispatch point automatically — no "return to office" step.
        $driver->update(['is_available' => true]);

        if ($booking->customer) {
            $pushNotifications->send(
                $booking->customer,
                'trip_completed',
                __('Trip Completed'),
                __('Your trip for booking #:number has been completed. Thank you for riding with us!', ['number' => $booking->booking_number]),
                route('customer.bookings.show', $booking),
                $booking->id,
                ['booking_number' => $booking->booking_number]
            );
        }

        return back()->with('status', __('Ride completed.'));
    }
}
