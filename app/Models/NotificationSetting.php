<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class NotificationSetting extends Model
{
    public const CACHE_KEY = 'notification.settings';

    public const EVENTS = [
        'booking_created' => 'Booking Created',
        'booking_confirmed' => 'Booking Confirmed',
        'driver_assigned' => 'Driver Assigned',
        'payment_successful' => 'Payment Successful',
        'booking_cancelled' => 'Cancellation',
        'booking_status_updated' => 'Booking Status Updated',
    ];

    protected $fillable = [
        'event_type',
        'label',
        'email_enabled',
        'admin_panel_enabled',
        'sms_enabled',
        'push_enabled',
    ];

    protected function casts(): array
    {
        return [
            'email_enabled' => 'boolean',
            'admin_panel_enabled' => 'boolean',
            'sms_enabled' => 'boolean',
            'push_enabled' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    /**
     * @return array<string, self>
     */
    public static function allByEvent(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return self::all()->keyBy('event_type')->all();
        });
    }

    public static function forEvent(string $eventType): ?self
    {
        return self::allByEvent()[$eventType] ?? null;
    }

    /**
     * The Laravel notification channel names ("mail", "database") enabled
     * for this event, based on admin-configured preferences.
     *
     * @return array<int, string>
     */
    public function activeChannels(): array
    {
        $channels = [];

        if ($this->email_enabled) {
            $channels[] = 'mail';
        }

        if ($this->admin_panel_enabled) {
            $channels[] = 'database';
        }

        // SMS and push are intentionally excluded — no provider is wired up
        // yet, even if a future migration flips these flags to true.

        return $channels;
    }
}
