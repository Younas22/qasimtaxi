<?php

namespace App\Services\Payments;

use App\Models\Booking;
use App\Models\Currency;
use App\Models\PaymentGateway;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * PayPal Checkout via the Orders v2 REST API directly (no SDK package —
 * PayPal's official PHP SDK adds a fair amount of weight for three simple
 * calls: get a token, create an order, capture it). Same admin-managed
 * credentials pattern as Stripe — see StripePaymentService.
 */
class PayPalPaymentService
{
    private function gateway(): PaymentGateway
    {
        $gateway = PaymentGateway::where('code', 'paypal')->first();

        if (! $gateway || ! $gateway->isReady()) {
            throw new RuntimeException('PayPal is not configured or enabled.');
        }

        return $gateway;
    }

    private function baseUrl(): string
    {
        return $this->gateway()->isLive()
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    /**
     * client_credentials OAuth2 token — short-lived (a few hours), so this
     * is simply requested fresh for each order/capture rather than cached;
     * these two calls per checkout aren't frequent enough to matter.
     */
    private function accessToken(): string
    {
        $gateway = $this->gateway();

        $response = Http::asForm()
            ->withBasicAuth($gateway->activeKey1(), $gateway->activeKey2())
            ->post($this->baseUrl().'/v1/oauth2/token', [
                'grant_type' => 'client_credentials',
            ])
            ->throw();

        return $response->json('access_token');
    }

    /**
     * Creates an order for the booking's still-owed balance and returns
     * the "approve" link to redirect the customer to.
     */
    public function createOrderApprovalUrl(Booking $booking): string
    {
        $response = Http::withToken($this->accessToken())
            ->post($this->baseUrl().'/v2/checkout/orders', [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'reference_id' => $booking->booking_number,
                    'description' => trim($booking->pickup_location.' → '.$booking->dropoff_location, ' →'),
                    'amount' => [
                        'currency_code' => Currency::defaultCode(),
                        'value' => number_format((float) $booking->fare_amount, 2, '.', ''),
                    ],
                ]],
                'application_context' => [
                    'brand_name' => setting('company_name', config('app.name')),
                    'user_action' => 'PAY_NOW',
                    'return_url' => route('booking.pay.paypal.return', $booking->booking_number),
                    'cancel_url' => route('booking.pay.cancel', [$booking->booking_number, 'paypal']),
                ],
            ])
            ->throw()
            ->json();

        $approveLink = collect($response['links'] ?? [])->firstWhere('rel', 'approve');

        if (! $approveLink) {
            throw new RuntimeException('PayPal did not return an approval link.');
        }

        return $approveLink['href'];
    }

    /**
     * Captures a previously-approved order. Returns the raw PayPal
     * response — the caller checks ['status'] === 'COMPLETED' and pulls
     * the capture id for transaction_id.
     *
     * @return array<string, mixed>
     */
    public function captureOrder(string $orderId): array
    {
        // PayPal's capture endpoint rejects a truly bodyless POST with
        // "400 MALFORMED_REQUEST_JSON" — Laravel's HTTP client sends no
        // body at all when post() is called with no $data argument, which
        // PayPal's parser chokes on. An explicit empty JSON object is what
        // PayPal itself documents for a capture with no payment_source
        // override, so it's sent here rather than relying on the default.
        return Http::withToken($this->accessToken())
            ->post($this->baseUrl()."/v2/checkout/orders/{$orderId}/capture", (object) [])
            ->throw()
            ->json();
    }
}
