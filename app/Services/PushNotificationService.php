<?php

namespace App\Services;

use App\Jobs\SendPushNotificationJob;
use App\Models\Admin;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\PushNotificationSetting;
use Illuminate\Database\Eloquent\Model;

/**
 * The single choke point every browser push notification in this
 * application must pass through — see the "Most Important Architecture
 * Rule" this class implements: nothing sends Web Push directly.
 *
 *   Controller / existing Notification class
 *          ↓
 *   PushNotificationService::send()
 *          ↓
 *   master switch → role switch → event-type switch
 *          ↓
 *   queued job → actual Web Push delivery → invalid-subscription cleanup
 *
 * Kept as plain PHP (not itself a Notification) so it can be called both
 * from the WebPushChannel (for the existing Mail-based Notification
 * classes that opt into it) and directly, for roles/events — Driver, and
 * some admin/customer events — that have no existing Notification class at
 * all yet.
 */
class PushNotificationService
{
    /**
     * @param  array<string, mixed>  $data  Extra fields merged into the push payload's "data" object (e.g. booking_number) — never put sensitive info here, it is visible inside the browser's notification event.
     */
    public function send(
        Model $recipient,
        string $eventType,
        string $title,
        string $body,
        ?string $url = null,
        ?int $bookingId = null,
        array $data = []
    ): void {
        if (! $this->isAllowed($recipient, $eventType)) {
            return;
        }

        $subscriptionIds = $recipient->pushSubscriptions()->pluck('id')->all();

        if (empty($subscriptionIds)) {
            return;
        }

        $payload = [
            'title' => $title,
            'body' => $body,
            'icon' => $this->brandIconUrl(),
            'badge' => $this->brandIconUrl(),
            'sound' => PushNotificationSetting::current()->notification_sound_url,
            'url' => $url,
            'type' => $eventType,
            'booking_id' => $bookingId,
            'data' => $data,
        ];

        // Dispatched, never sent inline — a slow/unreachable push service
        // (or a transient DNS hiccup) must never add latency to the
        // request that triggered it (booking creation, payment, etc).
        SendPushNotificationJob::dispatch($subscriptionIds, $payload);
    }

    /**
     * Sends immediately (no queue) to every subscription the given
     * recipient holds, bypassing the event-type check but NOT the master/
     * role switches — used only by the admin "Send Test Notification"
     * button, where the admin is actively waiting for a result.
     *
     * @return array{sent: bool, message: string}
     */
    public function sendTest(Model $recipient): array
    {
        $settings = PushNotificationSetting::current();

        if (! $settings->push_notifications_enabled) {
            return ['sent' => false, 'message' => __('Browser push notifications are currently disabled.')];
        }

        $role = $this->roleOf($recipient);

        if (! $role || ! $settings->{"push_{$role}_enabled"}) {
            return ['sent' => false, 'message' => __('Push notifications are disabled for your role.')];
        }

        $subscriptionIds = $recipient->pushSubscriptions()->pluck('id')->all();

        if (empty($subscriptionIds)) {
            return ['sent' => false, 'message' => __('No browser is subscribed yet on this account — click "Enable Notifications" first.')];
        }

        SendPushNotificationJob::dispatchSync($subscriptionIds, [
            'title' => __('Test Notification'),
            'body' => __('LimoSchedule browser notifications are working correctly.'),
            'icon' => $this->brandIconUrl(),
            'badge' => $this->brandIconUrl(),
            'sound' => $settings->notification_sound_url,
            'url' => null,
            'type' => 'test',
            'booking_id' => null,
            'data' => [],
        ]);

        return ['sent' => true, 'message' => __('Test notification sent.')];
    }

    /**
     * The master → role → event-type permission chain (spec §15) — every
     * caller of send() goes through this, no exceptions.
     */
    public function isAllowed(Model $recipient, string $eventType): bool
    {
        $settings = PushNotificationSetting::current();

        if (! $settings->push_notifications_enabled) {
            return false;
        }

        $role = $this->roleOf($recipient);

        if (! $role) {
            return false;
        }

        if (! $settings->{"push_{$role}_enabled"}) {
            return false;
        }

        $column = "push_{$role}_{$eventType}";

        // Unknown/custom event types (not one of the predefined granular
        // toggles) default to allowed — the role switch above already
        // gated it; the per-event columns only narrow further.
        if (! in_array($column, $settings->getFillable(), true)) {
            return true;
        }

        return (bool) $settings->{$column};
    }

    public function roleOf(Model $recipient): ?string
    {
        return match (true) {
            $recipient instanceof Admin => 'admin',
            $recipient instanceof Customer => 'customer',
            $recipient instanceof Driver => 'driver',
            default => null,
        };
    }

    /**
     * The site's favicon, read live from Settings on every send — never a
     * duplicated/hardcoded asset. A notification icon renders tiny (much
     * closer to a favicon's own size than a full logo's), so the favicon
     * reads clearly where a wordmark-shaped logo would just blur into a
     * smudge. If the admin changes the favicon, the very next push
     * automatically uses the new one.
     */
    public function brandIconUrl(): ?string
    {
        return setting('favicon_url') ?: asset('favicon.ico');
    }
}
