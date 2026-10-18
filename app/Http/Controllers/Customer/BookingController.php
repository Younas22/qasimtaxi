<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Notifications\BookingCancelledNotification;
use App\Services\AdminBookingNotifier;
use App\Services\DriverDispatchService;
use App\Services\PushNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BookingController extends Controller
{
    /**
     * Statuses a customer may still cancel from their side.
     */
    private const CANCELLABLE_STATUSES = ['pending', 'confirmed', 'assigned'];

    /**
     * Customer-facing status filter options. Kept distinct from the admin
     * panel's Booking::STATUSES list only in that no separate "assigned"
     * option is shown — customers see it as "On the Way" instead (see the
     * <x-customer.status-badge> component).
     */
    private const FILTER_STATUSES = [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'assigned' => 'On the Way',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    public function index(Request $request): View
    {
        return $this->list($request, __('My Bookings'));
    }

    /**
     * The in-panel "Book a Ride" screen — reuses the exact same public
     * booking widget component the homepage uses (<x-booking-search-box>),
     * just rendered inside the authenticated app shell so a logged-in
     * customer never has to leave the panel to start a booking. The
     * component is fully self-contained (its own Google Maps loading,
     * fare-quote AJAX, and submit handling) and already detects a logged-in
     * customer guard on submit, so nothing about the booking engine itself
     * changes here.
     */
    public function create(): View
    {
        return view('customer.bookings.create');
    }

    public function upcoming(Request $request): View
    {
        $search = $request->query('search');

        $bookings = Auth::guard('customer')->user()->bookings()
            ->with(['vehicle.category', 'driver'])
            ->whereIn('status', self::CANCELLABLE_STATUSES)
            ->where('pickup_datetime', '>=', now())
            ->when($search, fn ($q) => $q->where(fn ($q) => $q
                ->where('booking_number', 'like', "%{$search}%")
                ->orWhere('pickup_location', 'like', "%{$search}%")
                ->orWhere('dropoff_location', 'like', "%{$search}%")
            ))
            ->orderBy('pickup_datetime')
            ->paginate(9)
            ->withQueryString();

        return view('customer.bookings.upcoming', [
            'bookings' => $bookings,
            'search' => $search,
            'cancellableStatuses' => self::CANCELLABLE_STATUSES,
        ]);
    }

    public function completed(Request $request): View
    {
        $customer = Auth::guard('customer')->user();
        $search = $request->query('search');

        $bookings = $customer->bookings()
            ->with(['vehicle.category', 'driver', 'review'])
            ->where('status', 'completed')
            ->when($search, fn ($q) => $q->where(fn ($q) => $q
                ->where('booking_number', 'like', "%{$search}%")
                ->orWhere('pickup_location', 'like', "%{$search}%")
                ->orWhere('dropoff_location', 'like', "%{$search}%")
            ))
            ->latest('pickup_datetime')
            ->paginate(9)
            ->withQueryString();

        $totalPaid = $customer->bookings()->where('status', 'completed')->sum('fare_amount');

        return view('customer.bookings.completed', [
            'bookings' => $bookings,
            'search' => $search,
            'totalPaid' => $totalPaid,
        ]);
    }

    public function cancelled(Request $request): View
    {
        $search = $request->query('search');

        $bookings = Auth::guard('customer')->user()->bookings()
            ->with(['vehicle.category', 'driver'])
            ->where('status', 'cancelled')
            ->when($search, fn ($q) => $q->where(fn ($q) => $q
                ->where('booking_number', 'like', "%{$search}%")
                ->orWhere('pickup_location', 'like', "%{$search}%")
                ->orWhere('dropoff_location', 'like', "%{$search}%")
            ))
            ->latest('updated_at')
            ->paginate(9)
            ->withQueryString();

        return view('customer.bookings.cancelled', [
            'bookings' => $bookings,
            'search' => $search,
        ]);
    }

    public function show(Booking $booking, DriverDispatchService $dispatchService): View
    {
        abort_unless($booking->customer_id === Auth::guard('customer')->id(), 404);

        $booking->load(['vehicle.category', 'driver', 'review']);

        $dispatch = in_array($booking->status, ['confirmed', 'assigned'], true)
            ? $dispatchService->dispatchInfoFor($booking)
            : null;

        return view('customer.bookings.show', compact('booking', 'dispatch'));
    }

    public function cancel(Request $request, Booking $booking, PushNotificationService $pushNotifications, AdminBookingNotifier $adminNotifier): RedirectResponse
    {
        abort_unless($booking->customer_id === Auth::guard('customer')->id(), 404);

        if (! in_array($booking->status, self::CANCELLABLE_STATUSES, true)) {
            return back()->with('error', 'This booking can no longer be cancelled.');
        }

        $data = $request->validate([
            'cancellation_reason' => ['required', Rule::in(array_keys(Booking::CANCELLATION_REASONS))],
            'cancellation_note' => ['nullable', 'string', 'max:500'],
        ]);

        $reason = $data['cancellation_reason'];
        if ($reason === 'other' && filled($data['cancellation_note'] ?? null)) {
            $reason = $data['cancellation_note'];
        }

        $booking->update([
            'status' => 'cancelled',
            'cancellation_reason' => $reason,
            'cancelled_by' => 'customer',
            // Nothing was ever charged, so there's nothing to refund; a paid
            // booking needs an admin-processed refund.
            'refund_status' => $booking->payment_status === 'paid' ? 'pending' : 'not_applicable',
        ]);

        // A customer cancelling their own booking previously notified
        // nobody at all — admins found out only by noticing the status had
        // changed. Reusing the existing admin BookingCancelledNotification
        // (mail/in-app/push, per Settings → Notifications) closes that gap.
        $adminNotifier->send(new BookingCancelledNotification($booking));

        if ($booking->driver_id) {
            $booking->loadMissing('driver');

            $pushNotifications->send(
                $booking->driver,
                'booking_cancelled',
                __('Booking Cancelled'),
                __('Booking #:number has been cancelled by the customer.', ['number' => $booking->booking_number]),
                route('driver.bookings.show', $booking),
                $booking->id,
                ['booking_number' => $booking->booking_number]
            );
        }

        return back()->with('status', "Booking {$booking->booking_number} has been cancelled.");
    }

    /**
     * @param  array<int, string>|null  $lockedStatuses  Fixed status set for the Completed/Cancelled shortcuts; null means "All Bookings", where the status filter dropdown is shown instead.
     */
    private function list(Request $request, string $heading, ?array $lockedStatuses = null): View
    {
        $search = $request->query('search');
        $statusFilter = $lockedStatuses ? null : $request->query('status');

        $bookings = Auth::guard('customer')->user()->bookings()
            ->with(['vehicle.category', 'driver'])
            ->when($lockedStatuses, fn ($q) => $q->whereIn('status', $lockedStatuses))
            ->when($statusFilter, fn ($q) => $q->where('status', $statusFilter))
            ->when($search, fn ($q) => $q->where(fn ($q) => $q
                ->where('booking_number', 'like', "%{$search}%")
                ->orWhere('pickup_location', 'like', "%{$search}%")
                ->orWhere('dropoff_location', 'like', "%{$search}%")
            ))
            ->latest('pickup_datetime')
            ->paginate(10)
            ->withQueryString();

        return view('customer.bookings.index', [
            'bookings' => $bookings,
            'heading' => $heading,
            'search' => $search,
            'statusFilter' => $statusFilter,
            'showStatusFilter' => is_null($lockedStatuses),
            'filterStatuses' => self::FILTER_STATUSES,
        ]);
    }
}
