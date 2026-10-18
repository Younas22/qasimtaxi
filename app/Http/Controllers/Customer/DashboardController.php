<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Services\DriverDispatchService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(DriverDispatchService $dispatchService): View
    {
        $customer = Auth::guard('customer')->user();

        $stats = [
            'total' => $customer->bookings()->count(),
            'completed' => $customer->bookings()->where('status', 'completed')->count(),
            'upcoming' => $customer->bookings()
                ->whereIn('status', ['pending', 'confirmed', 'assigned'])
                ->where('pickup_datetime', '>=', now())
                ->count(),
            'totalSpent' => $customer->bookings()->where('status', 'completed')->sum('fare_amount'),
        ];

        // "Your ride" on the home screen: an in-progress ride takes priority
        // over a merely-upcoming one (it's the more urgent, more relevant
        // thing to show first), then falls back to the next scheduled ride.
        $activeRide = $customer->bookings()
            ->with(['vehicle.category', 'driver'])
            ->where('status', 'in_progress')
            ->latest('ride_started_at')
            ->first();

        $nextRide = $activeRide ?? $customer->bookings()
            ->with(['vehicle.category', 'driver'])
            ->whereIn('status', ['pending', 'confirmed', 'assigned'])
            ->where('pickup_datetime', '>=', now())
            ->orderBy('pickup_datetime')
            ->first();

        // A compact dispatch summary (status/distance/ETA text only, no
        // map) for the home screen — the full live map with polling stays
        // on the booking detail page to avoid running two Google Maps
        // instances/polling loops for the same ride at once. Reuses the
        // exact same DriverDispatchService the booking detail page uses.
        $nextRideDispatch = ($nextRide && in_array($nextRide->status, ['confirmed', 'assigned'], true))
            ? $dispatchService->dispatchInfoFor($nextRide)
            : null;

        $recentBookings = $customer->bookings()
            ->with(['vehicle.category', 'driver'])
            ->latest()
            ->limit(5)
            ->get();

        $favoriteVehicles = $customer->favoriteVehicles()
            ->with('category')
            ->orderByPivot('created_at', 'desc')
            ->limit(4)
            ->get();

        // Promotions/coupons temporarily hidden from the dashboard — shown
        // in their place instead, matching the sidebar's Wallet/Reviews hide.
        $recommendedVehicles = Vehicle::active()->with('category')->inRandomOrder()->limit(6)->get();

        return view('customer.dashboard', compact(
            'stats',
            'nextRide',
            'nextRideDispatch',
            'recentBookings',
            'favoriteVehicles',
            'recommendedVehicles'
        ));
    }
}
