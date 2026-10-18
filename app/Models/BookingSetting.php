<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class BookingSetting extends Model
{
    public const CACHE_KEY = 'booking.settings';

    public const DEFAULT_WHATSAPP_TEMPLATE = "Hello {customer_name}, this is regarding your booking {booking_number} ({booking_type}).\n\nPickup: {pickup_location}\nDrop-off: {dropoff_location}\nDate: {pickup_date}\nTime: {pickup_time}\nVehicle: {vehicle_name}\nFare: {fare_amount}\n\nPlease confirm your booking. Thank you!";

    protected $fillable = [
        'manual_booking_enabled',
        'website_booking_enabled',
        'guest_booking_enabled',
        'voice_search_enabled',
        'auto_confirmation_enabled',
        'driver_auto_assignment_enabled',
        'whatsapp_number',
        'whatsapp_message_template',
    ];

    protected function casts(): array
    {
        return [
            'manual_booking_enabled' => 'boolean',
            'website_booking_enabled' => 'boolean',
            'guest_booking_enabled' => 'boolean',
            'voice_search_enabled' => 'boolean',
            'auto_confirmation_enabled' => 'boolean',
            'driver_auto_assignment_enabled' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    public static function current(): self
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return self::firstOrCreate(['id' => 1], [
                'manual_booking_enabled' => false,
                'website_booking_enabled' => true,
                'guest_booking_enabled' => true,
                'voice_search_enabled' => false,
                'auto_confirmation_enabled' => false,
                'driver_auto_assignment_enabled' => false,
                'whatsapp_number' => null,
                'whatsapp_message_template' => self::DEFAULT_WHATSAPP_TEMPLATE,
            ]);
        });
    }
}
