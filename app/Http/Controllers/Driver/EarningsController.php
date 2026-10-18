<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EarningsController extends Controller
{
    public function index(): View
    {
        $driver = Auth::guard('driver')->user();
        $rate = (float) $driver->commission_rate / 100;

        $thisMonthFare = $driver->bookings()
            ->whereBetween('pickup_datetime', [now()->startOfMonth(), now()->endOfMonth()])
            ->where('payment_status', 'paid')
            ->sum('fare_amount');

        $lastMonthFare = $driver->bookings()
            ->whereBetween('pickup_datetime', [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()])
            ->where('payment_status', 'paid')
            ->sum('fare_amount');

        $totalFare = $driver->bookings()->where('payment_status', 'paid')->sum('fare_amount');

        $stats = [
            'thisMonth' => $thisMonthFare * $rate,
            'lastMonth' => $lastMonthFare * $rate,
            'total' => $totalFare * $rate,
        ];

        $bookings = $driver->bookings()
            ->with(['vehicle.category', 'customer'])
            ->where('payment_status', 'paid')
            ->latest('pickup_datetime')
            ->paginate(10);

        return view('driver.earnings.index', [
            'stats' => $stats,
            'commissionRate' => $driver->commission_rate,
            'bookings' => $bookings,
        ]);
    }
}
