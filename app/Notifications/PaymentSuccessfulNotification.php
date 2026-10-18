<?php

namespace App\Notifications;

class PaymentSuccessfulNotification extends BookingNotification
{
    public function eventType(): string
    {
        return 'payment_successful';
    }

    protected function title(): string
    {
        return 'Payment Successful';
    }

    protected function message(): string
    {
        return 'Payment of '.currency($this->booking->fare_amount)." received for booking {$this->booking->booking_number}.";
    }

    protected function mailSubject(): string
    {
        return "Payment Received — {$this->booking->booking_number}";
    }

    protected function mailLines(): array
    {
        return [
            "Payment has been received for booking {$this->booking->booking_number}.",
            "Customer: {$this->booking->customer?->name}",
            'Amount: '.currency($this->booking->fare_amount),
        ];
    }
}
