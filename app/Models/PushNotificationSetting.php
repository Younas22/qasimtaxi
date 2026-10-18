<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Single-row settings singleton (same pattern as Setting/BookingSetting)
 * dedicated to browser push notifications: a master kill switch, three
 * per-role switches, and a boolean per granular event type per role. See
 * the migration for why this is a dedicated table rather than reusing
 * NotificationSetting.
 */
class PushNotificationSetting extends Model
{
    public const CACHE_KEY = 'push_notification.settings';

    /**
     * Event-type columns grouped by role, in display order — used to
     * render/save the admin settings grid without hardcoding the list in
     * three separate places.
     *
     * @return array<string, array<string, string>>
     */
    public const ADMIN_EVENTS = [
        'push_admin_new_booking' => 'New Booking',
        'push_admin_booking_cancelled' => 'Booking Cancelled',
        'push_admin_payment_received' => 'Payment Received',
        'push_admin_new_customer' => 'New Customer',
        'push_admin_new_driver' => 'New Driver',
        'push_admin_driver_status_update' => 'Driver Status Update',
        'push_admin_booking_status_update' => 'Booking Status Update',
        'push_admin_system_alerts' => 'System Alerts',
    ];

    public const DRIVER_EVENTS = [
        'push_driver_booking_assigned' => 'New Booking Assigned',
        'push_driver_booking_updated' => 'Booking Updated',
        'push_driver_booking_cancelled' => 'Booking Cancelled',
        'push_driver_pickup_reminder' => 'Pickup Reminder',
        'push_driver_customer_update' => 'Customer Update',
        'push_driver_trip_update' => 'Payment / Trip Update',
        'push_driver_dispatch_update' => 'Dispatch Update',
    ];

    public const CUSTOMER_EVENTS = [
        'push_customer_booking_created' => 'Booking Created',
        'push_customer_booking_confirmed' => 'Booking Confirmed',
        'push_customer_driver_assigned' => 'Driver Assigned',
        'push_customer_driver_accepted' => 'Driver Accepted',
        'push_customer_driver_arriving' => 'Driver Arriving',
        'push_customer_trip_started' => 'Trip Started',
        'push_customer_trip_completed' => 'Trip Completed',
        'push_customer_booking_cancelled' => 'Booking Cancelled',
        'push_customer_payment_received' => 'Payment Received',
        'push_customer_invoice_ready' => 'Invoice Ready',
    ];

    /**
     * A plain property initializer can't spread the result of a function
     * call (array_keys() isn't a compile-time constant expression), so
     * this overrides getFillable() instead — same effect, just computed at
     * runtime rather than declared as a property default.
     *
     * @return array<int, string>
     */
    public function getFillable(): array
    {
        return [
            'push_notifications_enabled',
            'push_admin_enabled',
            'push_customer_enabled',
            'push_driver_enabled',
            'notification_sound',
            ...array_keys(self::ADMIN_EVENTS),
            ...array_keys(self::DRIVER_EVENTS),
            ...array_keys(self::CUSTOMER_EVENTS),
        ];
    }

    protected function casts(): array
    {
        // Every fillable column is a boolean toggle except the uploaded
        // sound filename, which stays a plain string.
        return array_fill_keys(
            array_diff($this->getFillable(), ['notification_sound']),
            'boolean'
        );
    }

    /**
     * Full URL of the admin-uploaded custom notification sound, or null to
     * fall back to the browser's own default notification sound. Read live
     * on every push send (see PushNotificationService::send()) — never
     * cached separately from the settings row itself, so a re-upload takes
     * effect on the very next notification with no extra cache-busting.
     */
    public function getNotificationSoundUrlAttribute(): ?string
    {
        return $this->notification_sound
            ? asset('uploads/push-sounds/'.$this->notification_sound)
            : null;
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    public static function current(): self
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return self::firstOrCreate(['id' => 1]);
        });
    }
}
