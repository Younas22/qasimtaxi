<?php

namespace App\Notifications;

use App\Channels\SafeMailChannel;
use App\Models\Booking;
use App\Models\EmailSetting;
use App\Models\NotificationSetting;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Shared plumbing for the four booking lifecycle notifications. Which
 * channels actually fire is decided per-event by the admin-configured
 * NotificationSetting row, not hardcoded here.
 */
abstract class BookingNotification extends Notification
{
    public function __construct(public Booking $booking)
    {
    }

    abstract public function eventType(): string;

    abstract protected function title(): string;

    abstract protected function message(): string;

    abstract protected function mailSubject(): string;

    /**
     * @return array<int, string>
     */
    abstract protected function mailLines(): array;

    /**
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        $channels = NotificationSetting::forEvent($this->eventType())?->activeChannels() ?? ['database'];

        // The on-demand copy AdminBookingNotifier sends to the configured
        // admin inbox is mail-only — in-app and push belong to the Admin rows.
        if ($notifiable instanceof AnonymousNotifiable) {
            return in_array('mail', $channels, true) ? [SafeMailChannel::class] : [];
        }

        // With an admin inbox configured, that inbox gets the email instead
        // of every admin login address (which may not be a real mailbox).
        if (EmailSetting::current()->adminNotificationEmails() !== []) {
            $channels = array_diff($channels, ['mail']);
        }

        $channels = array_values(array_map(
            fn ($channel) => $channel === 'mail' ? SafeMailChannel::class : $channel,
            $channels
        ));

        if (! $this->sendsWebPush()) {
            return $channels;
        }

        // Browser push has its own independent master/role/event-type
        // switches (Settings → Notifications → Browser Push) entirely
        // separate from the mail/database toggles above — see
        // PushNotificationService, which WebPushChannel delegates to and
        // which decides for itself whether this actually goes out.
        $channels[] = \App\Channels\WebPushChannel::class;

        return $channels;
    }

    protected function sendsWebPush(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function toWebPush(mixed $notifiable): ?array
    {
        return [
            'event_type' => $this->pushEventType(),
            'title' => $this->title(),
            'body' => $this->message(),
            'url' => route('admin.bookings.edit', $this->booking),
            'booking_id' => $this->booking->id,
            'data' => ['booking_number' => $this->booking->booking_number],
        ];
    }

    /**
     * Maps this notification's existing eventType() (also used for the
     * in-app notification icon — see admin.notifications.index) onto the
     * matching PushNotificationSetting column suffix. Not a 1:1 rename:
     * the admin's granular push toggles (New Booking, Booking Cancelled,
     * Payment Received, Booking Status Update, ...) predate and are
     * broader than this base class's 4 concrete subclasses.
     */
    private function pushEventType(): string
    {
        return match ($this->eventType()) {
            'booking_created' => 'new_booking',
            'payment_successful' => 'payment_received',
            'booking_cancelled' => 'booking_cancelled',
            default => 'booking_status_update', // booking_confirmed
        };
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->mailSubject())
            ->greeting('Hello '.($notifiable->name ?? 'Admin').',');

        foreach ($this->mailLines() as $line) {
            $mail->line($line);
        }

        return $mail
            ->action('View Booking', route('admin.bookings.edit', $this->booking))
            ->salutation('— '.setting('company_name', config('app.name')));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(mixed $notifiable): array
    {
        return [
            'event_type' => $this->eventType(),
            'title' => $this->title(),
            'message' => $this->message(),
            'booking_id' => $this->booking->id,
            'booking_number' => $this->booking->booking_number,
            'url' => route('admin.bookings.edit', $this->booking),
        ];
    }
}
