<?php

namespace App\Notifications\Customer;

class PaymentCompletedNotification extends CustomerBookingNotification
{
    public function eventType(): string
    {
        return 'payment_successful';
    }

    protected function title(): string
    {
        return 'Payment Completed';
    }

    protected function message(): string
    {
        return 'Your payment of '.currency($this->booking->fare_amount)." for booking {$this->booking->booking_number} was received.";
    }

    protected function mailSubject(): string
    {
        return "Payment Received — {$this->booking->booking_number}";
    }

    protected function mailLines(): array
    {
        return [
            "We've received your payment for booking {$this->booking->booking_number}.",
            'Amount: '.currency($this->booking->fare_amount),
        ];
    }
}
