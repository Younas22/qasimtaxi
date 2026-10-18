<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $driver = Auth::guard('driver')->user();

        $todayBookings = $driver->bookings()
            ->with(['vehicle.category', 'customer'])
            ->whereDate('pickup_datetime', now()->toDateString())
            ->orderBy('pickup_datetime')
            ->get();

        $monthEarnings = $driver->bookings()
            ->whereBetween('pickup_datetime', [now()->startOfMonth(), now()->endOfMonth()])
            ->where('payment_status', 'paid')
            ->sum('fare_amount') * ((float) $driver->commission_rate / 100);

        $stats = [
            'todayTrips' => $todayBookings->count(),
            'monthEarnings' => $monthEarnings,
            'totalTrips' => $driver->bookings()->where('status', 'completed')->count(),
            'averageRating' => $driver->average_rating,
        ];

        // The ride a driver has been assigned but hasn't started yet — shown
        // as "Up Next / Ready to Start" whenever there's no in-progress ride
        // taking priority. Mirrors the customer dashboard's "next ride" card.
        $upcomingRide = $driver->bookings()
            ->with(['vehicle.category', 'customer'])
            ->where('status', 'assigned')
            ->where('pickup_datetime', '>=', now()->subHours(1))
            ->orderBy('pickup_datetime')
            ->first();

        return view('driver.dashboard', [
            'driver' => $driver,
            'stats' => $stats,
            'todayBookings' => $todayBookings,
            'activeRide' => $driver->activeBooking(),
            'upcomingRide' => $upcomingRide,
        ]);
    }
}
