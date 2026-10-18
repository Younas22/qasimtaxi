<?php

namespace App\Notifications;

use App\Models\Booking;

/**
 * Admin-side notice for status changes that have no dedicated notification
 * of their own (pending, assigned, in progress, completed) — confirmed and
 * cancelled keep their existing BookingConfirmed/BookingCancelled classes.
 */
class BookingStatusUpdatedNotification extends BookingNotification
{
    public function __construct(Booking $booking, public string $previousStatus)
    {
        parent::__construct($booking);
    }

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
        return "Booking {$this->booking->booking_number} changed from {$this->previousLabel()} to {$this->booking->status_label}.";
    }

    protected function mailSubject(): string
    {
        return "Booking {$this->booking->status_label} — {$this->booking->booking_number}";
    }

    protected function mailLines(): array
    {
        return [
            "Booking {$this->booking->booking_number} status changed from {$this->previousLabel()} to {$this->booking->status_label}.",
            "Customer: {$this->booking->customer?->name}",
            'Driver: '.($this->booking->driver?->name ?? '—'),
            "Pickup: {$this->booking->pickup_location}",
            "Dropoff: {$this->booking->dropoff_location}",
            'Pickup Date/Time: '.$this->booking->pickup_datetime?->format('M d, Y h:i A'),
        ];
    }

    private function previousLabel(): string
    {
        return Booking::STATUSES[$this->previousStatus] ?? $this->previousStatus;
    }
}
