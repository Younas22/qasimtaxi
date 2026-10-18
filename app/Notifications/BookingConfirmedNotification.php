<?php

namespace App\Notifications;

class BookingConfirmedNotification extends BookingNotification
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
        return "Booking {$this->booking->booking_number} for {$this->booking->customer?->name} was confirmed.";
    }

    protected function mailSubject(): string
    {
        return "Booking Confirmed — {$this->booking->booking_number}";
    }

    protected function mailLines(): array
    {
        return [
            "Booking {$this->booking->booking_number} has been confirmed.",
            "Customer: {$this->booking->customer?->name}",
            "Vehicle: {$this->booking->vehicle?->name}",
            'Pickup Date/Time: '.$this->booking->pickup_datetime?->format('M d, Y h:i A'),
        ];
    }
}
