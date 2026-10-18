<?php

namespace App\Notifications;

class BookingCreatedNotification extends BookingNotification
{
    public function eventType(): string
    {
        return 'booking_created';
    }

    protected function title(): string
    {
        return 'New Booking Created';
    }

    protected function message(): string
    {
        return "Booking {$this->booking->booking_number} was created for {$this->booking->customer?->name}.";
    }

    protected function mailSubject(): string
    {
        return "New Booking Created — {$this->booking->booking_number}";
    }

    protected function mailLines(): array
    {
        return [
            "A new booking has been created: {$this->booking->booking_number}.",
            "Customer: {$this->booking->customer?->name}",
            "Type: {$this->booking->type_label}",
            "Pickup: {$this->booking->pickup_location}",
            "Dropoff: {$this->booking->dropoff_location}",
            'Pickup Date/Time: '.$this->booking->pickup_datetime?->format('M d, Y h:i A'),
        ];
    }
}
