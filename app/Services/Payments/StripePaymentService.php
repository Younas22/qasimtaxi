<?php

namespace App\Services\Payments;

use App\Models\Booking;
use App\Models\Currency;
use App\Models\PaymentGateway;
use RuntimeException;
use Stripe\Checkout\Session;
use Stripe\StripeClient;

/**
 * Wraps Stripe Checkout — a hosted, PCI-compliant payment page, so this
 * app never has to touch a raw card number. Credentials come from the
 * PaymentGateway row (Admin → Payment Gateways), never from .env, so
 * switching sandbox/live or rotating a key is an admin action, not a
 * deploy.
 */
class StripePaymentService
{
    private function gateway(): PaymentGateway
    {
        $gateway = PaymentGateway::where('code', 'stripe')->first();

        if (! $gateway || ! $gateway->isReady()) {
            throw new RuntimeException('Stripe is not configured or enabled.');
        }

        return $gateway;
    }

    private function client(): StripeClient
    {
        // key_2 is the Secret Key (see config/payment_gateways.php) — the
        // only one Stripe's server-side SDK actually needs.
        return new StripeClient($this->gateway()->activeKey2());
    }

    /**
     * Creates a Checkout Session for the booking's still-owed balance and
     * returns its hosted URL to redirect the customer to. The session id
     * is threaded through success_url so the return route can look the
     * session back up and confirm what Stripe actually collected —
     * never trusting the redirect alone.
     */
    public function createCheckoutUrl(Booking $booking): string
    {
        $currencyCode = strtolower(Currency::defaultCode());

        $session = $this->client()->checkout->sessions->create([
            'mode' => 'payment',
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => $currencyCode,
                    'unit_amount' => (int) round((float) $booking->fare_amount * 100),
                    'product_data' => [
                        'name' => __('Booking :number', ['number' => $booking->booking_number]),
                        'description' => trim($booking->pickup_location.' → '.$booking->dropoff_location, ' →'),
                    ],
                ],
            ]],
            'customer_email' => $booking->customer?->email ?: null,
            'client_reference_id' => $booking->booking_number,
            'success_url' => route('booking.pay.stripe.return', $booking->booking_number).'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('booking.pay.cancel', [$booking->booking_number, 'stripe']),
            'metadata' => [
                'booking_number' => $booking->booking_number,
            ],
        ]);

        return $session->url;
    }

    /**
     * Re-fetches the session from Stripe by id (never trusting whatever
     * the browser's query string claims) to see whether it actually paid.
     */
    public function retrieveSession(string $sessionId): Session
    {
        return $this->client()->checkout->sessions->retrieve($sessionId);
    }
}
