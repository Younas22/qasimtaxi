<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\BookingCreationService;
use App\Services\Payments\PayPalPaymentService;
use App\Services\Payments\StripePaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * Lets a customer actually pay for a booking online — Stripe Checkout or
 * PayPal — once an admin has configured and enabled a gateway (Admin →
 * Payment Gateways). Reachable without being logged in, the same way
 * booking.confirmation/booking.invoice already are: whoever holds the
 * booking number (emailed/shown right after booking) can pay it.
 */
class BookingPaymentController extends Controller
{
    /**
     * Kicks off a payment: builds a hosted checkout session with the
     * chosen gateway and redirects the customer there.
     */
    public function pay(Request $request, string $bookingNumber, string $gateway, StripePaymentService $stripe, PayPalPaymentService $paypal): RedirectResponse
    {
        $request->merge(['gateway' => $gateway]);
        $request->validate(['gateway' => [Rule::in(['stripe', 'paypal'])]]);

        $booking = Booking::where('booking_number', $bookingNumber)->firstOrFail();

        if ($redirect = $this->guardAgainstUnpayable($booking)) {
            return $redirect;
        }

        try {
            $url = $gateway === 'stripe'
                ? $stripe->createCheckoutUrl($booking)
                : $paypal->createOrderApprovalUrl($booking);

            return redirect()->away($url);
        } catch (\Throwable $e) {
            Log::error("Failed to start {$gateway} checkout for booking {$booking->booking_number}: ".$e->getMessage());

            return redirect()
                ->route('booking.confirmation', $booking->booking_number)
                ->with('error', __('Sorry, we couldn\'t start the payment. Please try again or contact us.'));
        }
    }

    /**
     * Stripe redirects here after checkout. The session id is re-fetched
     * from Stripe itself (never trusted from the query string alone) to
     * confirm what was actually collected before marking anything paid.
     */
    public function stripeReturn(Request $request, string $bookingNumber, StripePaymentService $stripe, BookingCreationService $bookingCreation): RedirectResponse
    {
        $booking = Booking::where('booking_number', $bookingNumber)->firstOrFail();
        $sessionId = $request->query('session_id');

        if (! $sessionId) {
            return $this->paymentFailed($booking, __('Payment could not be confirmed.'));
        }

        try {
            $session = $stripe->retrieveSession($sessionId);
        } catch (\Throwable $e) {
            Log::error("Failed to retrieve Stripe session for booking {$booking->booking_number}: ".$e->getMessage());

            return $this->paymentFailed($booking, __('Payment could not be confirmed.'));
        }

        if ($session->payment_status !== 'paid') {
            return $this->paymentFailed($booking, __('Payment was not completed.'));
        }

        $this->markPaid($booking, 'stripe', (string) $session->payment_intent);
        $bookingCreation->confirmAfterPayment($booking);

        return $this->paymentSucceeded($booking);
    }

    /**
     * PayPal redirects here after the customer approves the order (as
     * ?token=<order id>) — that approval isn't itself a charge, so the
     * order is captured here before anything is marked paid.
     */
    public function paypalReturn(Request $request, string $bookingNumber, PayPalPaymentService $paypal, BookingCreationService $bookingCreation): RedirectResponse
    {
        $booking = Booking::where('booking_number', $bookingNumber)->firstOrFail();
        $orderId = $request->query('token');

        if (! $orderId) {
            return $this->paymentFailed($booking, __('Payment could not be confirmed.'));
        }

        try {
            $result = $paypal->captureOrder($orderId);
        } catch (\Throwable $e) {
            Log::error("Failed to capture PayPal order for booking {$booking->booking_number}: ".$e->getMessage());

            return $this->paymentFailed($booking, __('Payment could not be confirmed.'));
        }

        if (($result['status'] ?? null) !== 'COMPLETED') {
            return $this->paymentFailed($booking, __('Payment was not completed.'));
        }

        $captureId = data_get($result, 'purchase_units.0.payments.captures.0.id', $orderId);
        $this->markPaid($booking, 'paypal', $captureId);
        $bookingCreation->confirmAfterPayment($booking);

        return $this->paymentSucceeded($booking);
    }

    /**
     * The customer backed out of the hosted checkout page — no charge was
     * made, just send them back with a neutral notice.
     */
    public function cancel(string $bookingNumber, string $gateway): RedirectResponse
    {
        $booking = Booking::where('booking_number', $bookingNumber)->firstOrFail();

        return redirect()
            ->route('booking.confirmation', $booking->booking_number)
            ->with('error', __('Payment was cancelled — your booking is still reserved, and you can pay again any time.'));
    }

    /**
     * Bookings that are already paid, refunded, or cancelled have no
     * business starting a new charge.
     */
    private function guardAgainstUnpayable(Booking $booking): ?RedirectResponse
    {
        if ($booking->payment_status === 'paid') {
            return redirect()
                ->route('booking.confirmation', $booking->booking_number)
                ->with('status', __('This booking is already paid.'));
        }

        if ($booking->status === 'cancelled') {
            return redirect()
                ->route('booking.confirmation', $booking->booking_number)
                ->with('error', __('This booking has been cancelled and can no longer be paid.'));
        }

        return null;
    }

    private function markPaid(Booking $booking, string $gateway, string $transactionId): void
    {
        $booking->update([
            'payment_status' => 'paid',
            'paid_at' => now(),
            'payment_gateway' => $gateway,
            'transaction_id' => $transactionId,
        ]);

        if ($booking->customer) {
            $booking->customer->notify(new \App\Notifications\Customer\PaymentCompletedNotification($booking));
        }

        app(\App\Services\AdminBookingNotifier::class)->send(new \App\Notifications\PaymentSuccessfulNotification($booking));
    }

    private function paymentSucceeded(Booking $booking): RedirectResponse
    {
        return redirect()
            ->route('booking.confirmation', $booking->booking_number)
            ->with('status', __('Payment received — thank you! Your booking is fully paid.'));
    }

    private function paymentFailed(Booking $booking, string $message): RedirectResponse
    {
        return redirect()
            ->route('booking.confirmation', $booking->booking_number)
            ->with('error', $message);
    }
}
