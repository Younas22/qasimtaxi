<?php

namespace App\Notifications\Customer;

class DriverAssignedNotification extends CustomerBookingNotification
{
    public function eventType(): string
    {
        return 'driver_assigned';
    }

    protected function title(): string
    {
        return 'Driver Assigned';
    }

    protected function message(): string
    {
        return "{$this->booking->driver?->name} has been assigned to your booking {$this->booking->booking_number}.";
    }

    protected function mailSubject(): string
    {
        return "Driver Assigned — {$this->booking->booking_number}";
    }

    protected function mailLines(): array
    {
        return [
            "A driver has been assigned to your booking {$this->booking->booking_number}.",
            "Driver: {$this->booking->driver?->name}",
            "Vehicle: {$this->booking->vehicle?->name}",
            'Pickup Date/Time: '.$this->booking->pickup_datetime?->format('M d, Y h:i A'),
        ];
    }
}
