<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Registers/removes a browser's PushManager subscription for whichever of
 * the app's three guards (admin, customer, driver) is currently logged in
 * — resolved server-side from the session, exactly like every other
 * authenticated endpoint in this app; the frontend never gets to say who
 * it's subscribing on behalf of.
 */
class PushSubscriptionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $subscriber = $this->currentSubscriber();

        if (! $subscriber) {
            return response()->json(['message' => 'You must be logged in to enable notifications.'], 401);
        }

        $data = $request->validate([
            'endpoint' => ['required', 'string', 'max:2048', 'url'],
            'keys' => ['required', 'array'],
            'keys.p256dh' => ['required', 'string'],
            'keys.auth' => ['required', 'string'],
            'contentEncoding' => ['nullable', 'string', 'in:aesgcm,aes128gcm'],
        ]);

        $userAgent = (string) $request->userAgent();

        // Keyed on the base model — not $subscriber->pushSubscriptions() —
        // and deliberately so: endpoint_hash is globally unique (one browser
        // install = one push endpoint, full stop), so on a device someone
        // else already enabled notifications on, scoping this to the
        // current subscriber's own relation would try to INSERT a second
        // row for the same endpoint and crash on that unique constraint.
        // Going through the base model reassigns the existing row to
        // whoever is enabling it now, which is also the behavior a shared
        // device should have: the last account to explicitly hit "Enable"
        // here is who this browser notifies, not whoever did it first.
        PushSubscription::updateOrCreate(
            ['endpoint_hash' => PushSubscription::hashEndpoint($data['endpoint'])],
            [
                'subscribable_type' => $subscriber->getMorphClass(),
                'subscribable_id' => $subscriber->getKey(),
                'endpoint' => $data['endpoint'],
                'public_key' => $data['keys']['p256dh'],
                'auth_token' => $data['keys']['auth'],
                'content_encoding' => $data['contentEncoding'] ?? null,
                'device_name' => device_label($userAgent),
                'browser' => $this->browserFrom($userAgent),
                'platform' => $this->platformFrom($userAgent),
                'last_used_at' => now(),
            ]
        );

        return response()->json(['message' => 'Browser notifications enabled.']);
    }

    public function destroy(Request $request): JsonResponse
    {
        $subscriber = $this->currentSubscriber();

        if (! $subscriber) {
            return response()->json(['message' => 'You must be logged in.'], 401);
        }

        $data = $request->validate([
            'endpoint' => ['required', 'string', 'max:2048'],
        ]);

        // Unsubscribes only THIS browser/device — the account may still
        // have other subscribed devices, which stay untouched.
        $subscriber->pushSubscriptions()
            ->where('endpoint_hash', PushSubscription::hashEndpoint($data['endpoint']))
            ->delete();

        return response()->json(['message' => 'Browser notifications disabled on this device.']);
    }

    /**
     * Whether the CURRENT browser (identified by the endpoint it would
     * subscribe with, or simply "does this account have any subscription
     * at all" as a fallback before the endpoint is known) is already
     * subscribed — lets the toggle component render its initial state
     * without waiting on a client-side permission prompt.
     */
    public function status(Request $request): JsonResponse
    {
        $subscriber = $this->currentSubscriber();

        if (! $subscriber) {
            return response()->json(['subscribed' => false]);
        }

        $endpoint = $request->query('endpoint');

        $subscribed = $endpoint
            ? $subscriber->pushSubscriptions()->where('endpoint_hash', PushSubscription::hashEndpoint($endpoint))->exists()
            : $subscriber->pushSubscriptions()->exists();

        return response()->json(['subscribed' => $subscribed]);
    }

    private function currentSubscriber(): ?Model
    {
        foreach (['admin', 'customer', 'driver'] as $guard) {
            if ($user = Auth::guard($guard)->user()) {
                return $user;
            }
        }

        return null;
    }

    private function browserFrom(string $userAgent): ?string
    {
        return match (true) {
            (bool) preg_match('/edg\//i', $userAgent) => 'Edge',
            (bool) preg_match('/chrome\//i', $userAgent) => 'Chrome',
            (bool) preg_match('/crios\//i', $userAgent) => 'Chrome',
            (bool) preg_match('/firefox\//i', $userAgent) => 'Firefox',
            (bool) preg_match('/safari\//i', $userAgent) => 'Safari',
            default => null,
        };
    }

    private function platformFrom(string $userAgent): ?string
    {
        return match (true) {
            (bool) preg_match('/android/i', $userAgent) => 'Android',
            (bool) preg_match('/iphone|ipad/i', $userAgent) => 'iOS',
            (bool) preg_match('/windows/i', $userAgent) => 'Windows',
            (bool) preg_match('/mac os x/i', $userAgent) => 'macOS',
            (bool) preg_match('/linux/i', $userAgent) => 'Linux',
            default => null,
        };
    }
}
