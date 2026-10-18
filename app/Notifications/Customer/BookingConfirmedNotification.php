<?php

namespace App\Notifications\Customer;

class BookingConfirmedNotification extends CustomerBookingNotification
{
    public function eventType(): string
    {
        return 'booking_confirmed';
    }

    protected function title(): string
    {
        return 'Booking Confirmed';
    }

    protected function message(): string
    {
        return "Your booking {$this->booking->booking_number} has been confirmed.";
    }

    protected function mailSubject(): string
    {
        return "Booking Confirmed — {$this->booking->booking_number}";
    }

    protected function mailLines(): array
    {
        return [
            "Your booking {$this->booking->booking_number} has been confirmed.",
            "Pickup: {$this->booking->pickup_location}",
            "Drop-off: {$this->booking->dropoff_location}",
            'Pickup Date/Time: '.$this->booking->pickup_datetime?->format('M d, Y h:i A'),
        ];
    }
}
