<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * One browser/device's Web Push subscription (see PushNotificationService
 * for how these are actually pushed to). subscribable is polymorphic —
 * Admin, Customer, or Driver — since this app has no unified users table.
 */
class PushSubscription extends Model
{
    protected $fillable = [
        'subscribable_type',
        'subscribable_id',
        'endpoint',
        'endpoint_hash',
        'public_key',
        'auth_token',
        'content_encoding',
        'device_name',
        'browser',
        'platform',
        'last_used_at',
    ];

    protected function casts(): array
    {
        return [
            'last_used_at' => 'datetime',
        ];
    }

    public function subscribable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * SHA-256 of the endpoint — the actual unique/lookup key (see the
     * migration for why the raw endpoint itself can't be indexed).
     */
    public static function hashEndpoint(string $endpoint): string
    {
        return hash('sha256', $endpoint);
    }
}
