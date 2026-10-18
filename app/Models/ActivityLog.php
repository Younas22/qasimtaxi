<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

class ActivityLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'admin_id',
        'action',
        'description',
        'ip_address',
        'user_agent',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public static function record(string $action, string $description, ?Request $request = null, ?Admin $admin = null): self
    {
        $request ??= request();
        $admin ??= auth('admin')->user();

        return self::create([
            'admin_id' => $admin?->id,
            'action' => $action,
            'description' => $description,
            'ip_address' => $request?->ip(),
            'user_agent' => $request ? substr((string) $request->userAgent(), 0, 255) : null,
        ]);
    }
}
