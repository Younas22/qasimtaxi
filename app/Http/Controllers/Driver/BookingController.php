<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(Request $request): View
    {
        $statusFilter = $request->query('status');

        $bookings = Auth::guard('driver')->user()->bookings()
            ->with(['vehicle.category', 'customer'])
            ->when($statusFilter, fn ($q) => $q->where('status', $statusFilter))
            ->latest('pickup_datetime')
            ->paginate(10)
            ->withQueryString();

        return view('driver.bookings.index', [
            'bookings' => $bookings,
            'statusFilter' => $statusFilter,
            'filterStatuses' => Booking::STATUSES,
        ]);
    }

    public function show(Booking $booking): View
    {
        abort_unless($booking->driver_id === Auth::guard('driver')->id(), 404);

        $booking->load(['vehicle.category', 'customer', 'review']);

        return view('driver.bookings.show', compact('booking'));
    }
}
