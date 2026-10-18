<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingSetting;

/**
 * Builds a wa.me deep link for a booking, filling the admin-configured
 * message template with that booking's data. Used by the "Book Now" /
 * "Message on WhatsApp" action, which is only available while manual
 * booking is enabled and a WhatsApp number is configured.
 */
class WhatsAppBookingLinkGenerator
{
    public function isAvailable(): bool
    {
        $settings = BookingSetting::current();

        return $settings->manual_booking_enabled && filled($settings->whatsapp_number);
    }

    public function linkFor(Booking $booking): ?string
    {
        if (! $this->isAvailable()) {
            return null;
        }

        $settings = BookingSetting::current();
        $number = preg_replace('/\D+/', '', $settings->whatsapp_number);

        if ($number === '') {
            return null;
        }

        $message = $this->render($settings->whatsapp_message_template ?: BookingSetting::DEFAULT_WHATSAPP_TEMPLATE, $booking);

        return 'https://wa.me/'.$number.'?text='.rawurlencode($message);
    }

    public function render(string $template, Booking $booking): string
    {
        $booking->loadMissing(['customer', 'vehicle']);

        $tokens = [
            '{customer_name}' => $booking->customer?->name ?? 'Customer',
            '{booking_number}' => $booking->booking_number,
            '{booking_type}' => $booking->type_label,
            '{pickup_location}' => $booking->pickup_location,
            '{dropoff_location}' => $booking->dropoff_location,
            '{pickup_date}' => $booking->pickup_datetime?->format('M d, Y'),
            '{pickup_time}' => $booking->pickup_datetime?->format('h:i A'),
            '{vehicle_name}' => $booking->vehicle?->name ?? 'N/A',
            '{fare_amount}' => currency($booking->fare_amount),
            '{status}' => $booking->status_label,
            '{passengers}' => (string) $booking->passengers,
        ];

        return strtr($template, $tokens);
    }

    /**
     * @return array<string, string>
     */
    public static function availableTokens(): array
    {
        return [
            '{customer_name}' => 'Customer full name',
            '{booking_number}' => 'Booking reference number',
            '{booking_type}' => 'Booking type (e.g. Airport Transfer)',
            '{pickup_location}' => 'Pickup address',
            '{dropoff_location}' => 'Drop-off address',
            '{pickup_date}' => 'Pickup date',
            '{pickup_time}' => 'Pickup time',
            '{vehicle_name}' => 'Assigned vehicle name',
            '{fare_amount}' => 'Formatted fare amount',
            '{status}' => 'Booking status',
            '{passengers}' => 'Passenger count',
        ];
    }
}
