<?php

namespace App\Notifications\Customer;

/**
 * Customer-side counterpart to App\Notifications\BookingStatusUpdatedNotification.
 * No web push — trip started/completed already push via PushNotificationService.
 */
class BookingStatusUpdatedNotification extends CustomerBookingNotification
{
    public function eventType(): string
    {
        return 'booking_status_updated';
    }

    protected function sendsWebPush(): bool
    {
        return false;
    }

    protected function title(): string
    {
        return 'Booking Status Updated';
    }

    protected function message(): string
    {
        return "Your booking {$this->booking->booking_number} is now {$this->booking->status_label}.";
    }

    protected function mailSubject(): string
    {
        return "Booking {$this->booking->status_label} — {$this->booking->booking_number}";
    }

    protected function mailLines(): array
    {
        return array_values(array_filter([
            $this->statusLine(),
            "Pickup: {$this->booking->pickup_location}",
            "Drop-off: {$this->booking->dropoff_location}",
            'Pickup Date/Time: '.$this->booking->pickup_datetime?->format('M d, Y h:i A'),
            $this->booking->driver ? "Driver: {$this->booking->driver->name}" : null,
        ]));
    }

    private function statusLine(): string
    {
        return match ($this->booking->status) {
            'in_progress' => "Your trip for booking {$this->booking->booking_number} has started.",
            'completed' => "Your trip for booking {$this->booking->booking_number} has been completed. Thank you for riding with us!",
            default => "The status of your booking {$this->booking->booking_number} is now: {$this->booking->status_label}.",
        };
    }
}
