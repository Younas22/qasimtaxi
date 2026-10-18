<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class CustomerActiveSession extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'customer_id',
        'session_id',
        'ip_address',
        'user_agent',
        'device_label',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * The matching row's last_activity from Laravel's real session store,
     * or null if the underlying session has since expired/been garbage
     * collected — in which case this tracking row is stale and prunable.
     */
    public function lastActivity(): ?int
    {
        return DB::table('sessions')->where('id', $this->session_id)->value('last_activity');
    }

    public function isLive(): bool
    {
        return $this->lastActivity() !== null;
    }
}
