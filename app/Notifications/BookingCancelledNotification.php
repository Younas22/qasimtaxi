<?php

namespace App\Notifications;

class BookingCancelledNotification extends BookingNotification
{
    public function eventType(): string
    {
        return 'booking_cancelled';
    }

    protected function title(): string
    {
        return 'Booking Cancelled';
    }

    protected function message(): string
    {
        return "Booking {$this->booking->booking_number} for {$this->booking->customer?->name} was cancelled.";
    }

    protected function mailSubject(): string
    {
        return "Booking Cancelled — {$this->booking->booking_number}";
    }

    protected function mailLines(): array
    {
        return [
            "Booking {$this->booking->booking_number} has been cancelled.",
            "Customer: {$this->booking->customer?->name}",
            'Pickup Date/Time was: '.$this->booking->pickup_datetime?->format('M d, Y h:i A'),
        ];
    }
}
