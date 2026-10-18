<?php

namespace App\Notifications\Customer;

/**
 * Sent when a booking is created but not yet confirmed (e.g. awaiting
 * payment or manual confirmation), so the customer still gets an email
 * receipt straight away. Confirmed bookings get BookingConfirmedNotification
 * instead.
 */
class BookingReceivedNotification extends CustomerBookingNotification
{
    public function eventType(): string
    {
        return 'booking_created';
    }

    protected function sendsWebPush(): bool
    {
        return false;
    }

    protected function title(): string
    {
        return 'Booking Received';
    }

    protected function message(): string
    {
        return "We have received your booking {$this->booking->booking_number}.";
    }

    protected function mailSubject(): string
    {
        return "Booking Received — {$this->booking->booking_number}";
    }

    protected function mailLines(): array
    {
        return [
            "Thank you! We have received your booking {$this->booking->booking_number}. We will notify you as soon as it is confirmed.",
            "Type: {$this->booking->type_label}",
            "Pickup: {$this->booking->pickup_location}",
            "Drop-off: {$this->booking->dropoff_location}",
            'Pickup Date/Time: '.$this->booking->pickup_datetime?->format('M d, Y h:i A'),
            'Fare: '.currency($this->booking->fare_amount),
        ];
    }
}
