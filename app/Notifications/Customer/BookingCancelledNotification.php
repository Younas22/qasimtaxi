<?php

namespace App\Notifications\Customer;

class BookingCancelledNotification extends CustomerBookingNotification
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
        return "Your booking {$this->booking->booking_number} has been cancelled.";
    }

    protected function mailSubject(): string
    {
        return "Booking Cancelled — {$this->booking->booking_number}";
    }

    protected function mailLines(): array
    {
        return [
            "Your booking {$this->booking->booking_number} has been cancelled.",
            'Pickup Date/Time was: '.$this->booking->pickup_datetime?->format('M d, Y h:i A'),
        ];
    }
}
