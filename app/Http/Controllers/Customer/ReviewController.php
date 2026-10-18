<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(): View
    {
        $reviews = Auth::guard('customer')->user()->reviews()
            ->with(['driver', 'vehicle'])
            ->paginate(10);

        return view('customer.reviews.index', compact('reviews'));
    }

    public function create(Booking $booking): View|RedirectResponse
    {
        $this->authorizeReviewable($booking);

        if ($booking->review) {
            return redirect()->route('customer.reviews.index')->with('status', 'You already reviewed this trip.');
        }

        return view('customer.reviews.create', compact('booking'));
    }

    public function store(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorizeReviewable($booking);

        if ($booking->review) {
            return redirect()->route('customer.reviews.index')->with('status', 'You already reviewed this trip.');
        }

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        Review::create($data + [
            'customer_id' => $booking->customer_id,
            'booking_id' => $booking->id,
            'driver_id' => $booking->driver_id,
            'vehicle_id' => $booking->vehicle_id,
            'status' => 'pending',
        ]);

        return redirect()->route('customer.reviews.index')->with('status', 'Thank you! Your review has been submitted for approval.');
    }

    private function authorizeReviewable(Booking $booking): void
    {
        abort_unless($booking->customer_id === Auth::guard('customer')->id(), 404);
        abort_unless($booking->status === 'completed', 404);
    }
}
