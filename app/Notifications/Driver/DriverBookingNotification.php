<?php

namespace App\Notifications\Driver;

use App\Channels\WebPushChannel;
use App\Models\Booking;
use Illuminate\Notifications\Notification;

/**
 * Driver-facing booking lifecycle notifications — the driver counterpart to
 * App\Notifications\BookingNotification (admin) and
 * Customer\CustomerBookingNotification. Drivers get no mail channel (no
 * existing email templates for them, and push already covers the
 * time-sensitive cases this is used for), just an in-app notification
 * center entry (see Driver\NotificationController) plus browser push.
 *
 * Unlike the admin/customer base classes, this isn't split into one
 * subclass per event — every driver event today (assigned, cancelled) is a
 * single line of copy with no event-specific mail template to justify the
 * extra classes, so the title/message/event type are passed in directly.
 */
class DriverBookingNotification extends Notification
{
    public function __construct(
        public Booking $booking,
        public string $eventType,
        public string $title,
        public string $message,
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        return ['database', WebPushChannel::class];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(mixed $notifiable): array
    {
        return [
            'event_type' => $this->eventType,
            'title' => $this->title,
            'message' => $this->message,
            'booking_id' => $this->booking->id,
            'booking_number' => $this->booking->booking_number,
            'url' => route('driver.bookings.show', $this->booking),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function toWebPush(mixed $notifiable): ?array
    {
        return [
            'event_type' => $this->eventType,
            'title' => $this->title,
            'body' => $this->message,
            'url' => route('driver.bookings.show', $this->booking),
            'booking_id' => $this->booking->id,
            'data' => ['booking_number' => $this->booking->booking_number],
        ];
    }
}
