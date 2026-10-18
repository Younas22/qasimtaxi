<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class EmailSetting extends Model
{
    public const CACHE_KEY = 'email.settings';

    public const MAILERS = [
        'resend' => 'Resend',
        'smtp' => 'SMTP',
        'sendmail' => 'Sendmail',
        'log' => 'Log (development only)',
    ];

    protected $fillable = [
        'mailer',
        'resend_api_key',
        'from_address',
        'from_name',
        'admin_notification_email',
    ];

    protected function casts(): array
    {
        return [
            'resend_api_key' => 'encrypted',
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
                'mailer' => config('mail.default', 'log'),
                'from_address' => config('mail.from.address'),
                'from_name' => config('mail.from.name'),
            ]);
        });
    }

    /**
     * The inbox(es) admin booking notifications are mailed to, parsed from
     * the comma-separated admin_notification_email setting.
     *
     * @return array<int, string>
     */
    public function adminNotificationEmails(): array
    {
        return collect(explode(',', (string) $this->admin_notification_email))
            ->map(fn ($email) => trim($email))
            ->filter(fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Push these settings into the runtime mail config so Laravel's mail
     * manager picks them up for every mailer resolved after this call.
     */
    public function applyToRuntimeConfig(): void
    {
        config([
            'mail.default' => $this->mailer,
            'mail.from.address' => $this->from_address ?: config('mail.from.address'),
            'mail.from.name' => $this->from_name ?: config('mail.from.name'),
            'services.resend.key' => $this->resend_api_key ?: config('services.resend.key'),
        ]);
    }
}
