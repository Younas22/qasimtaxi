<?php

namespace App\Channels;

use App\Services\PushNotificationService;
use Illuminate\Notifications\Notification;

/**
 * Laravel notification channel adapter — lets the EXISTING Mail/database
 * notification classes (BookingNotification, CustomerBookingNotification,
 * PaymentSuccessfulNotification, ...) opt into browser push by adding
 * WebPushChannel::class to their via() array and implementing
 * toWebPush($notifiable): ?array, without duplicating any of the
 * master/role/event-type permission logic — that all still lives in
 * PushNotificationService, which this only ever delegates to.
 */
class WebPushChannel
{
    public function __construct(private readonly PushNotificationService $pushNotifications)
    {
    }

    public function send(mixed $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toWebPush')) {
            return;
        }

        $payload = $notification->toWebPush($notifiable);

        if (! $payload || empty($payload['title']) || empty($payload['body'])) {
            return;
        }

        $this->pushNotifications->send(
            $notifiable,
            $payload['event_type'] ?? 'system_alerts',
            $payload['title'],
            $payload['body'],
            $payload['url'] ?? null,
            $payload['booking_id'] ?? null,
            $payload['data'] ?? []
        );
    }
}
