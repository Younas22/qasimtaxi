<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\PushNotificationSetting;
use App\Models\PushSubscription;
use App\Models\Vehicle;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'bookings' => Booking::count(),
            'revenue' => Booking::where('payment_status', 'paid')->sum('fare_amount'),
            'vehicles' => Vehicle::count(),
            'drivers' => Driver::count(),
            'customers' => Customer::count(),
        ];

        // "What's happening right now" — the numbers an admin actually
        // needs first thing, not just all-time totals.
        $todayBookings = Booking::whereDate('pickup_datetime', now()->toDateString())->count();
        $todayRevenue = Booking::whereDate('pickup_datetime', now()->toDateString())
            ->where('payment_status', 'paid')
            ->sum('fare_amount');
        $pendingBookings = Booking::where('status', 'pending')->count();
        $unassignedBookings = Booking::whereIn('status', ['pending', 'confirmed'])->whereNull('driver_id')->count();
        $activeRides = Booking::where('status', 'in_progress')->count();

        $recentBookings = Booking::with(['customer', 'driver', 'vehicle'])
            ->latest()
            ->take(5)
            ->get();

        $onlineDrivers = Driver::where('is_online', true)->get();

        $fleetSummary = [
            'online' => $onlineDrivers->count(),
            'busy' => $onlineDrivers->filter(fn (Driver $d) => (bool) $d->activeBooking())->count(),
        ];

        $pushStatus = PushNotificationSetting::current();
        $pushSubscriptionCount = PushSubscription::count();

        return view('admin.dashboard', compact(
            'stats',
            'recentBookings',
            'fleetSummary',
            'todayBookings',
            'todayRevenue',
            'pendingBookings',
            'unassignedBookings',
            'activeRides',
            'pushStatus',
            'pushSubscriptionCount'
        ));
    }
}
