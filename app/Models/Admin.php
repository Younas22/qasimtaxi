<?php

namespace App\Models;

use App\Models\Concerns\HasPushSubscriptions;
use App\Notifications\Admin\ResetPassword;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasFactory, HasPushSubscriptions, Notifiable;

    /**
     * Laravel's default notification hardcodes a route name that doesn't
     * exist under the admin guard's prefixed routes — see the custom
     * notification class for details.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPassword($token));
    }

    protected $fillable = [
        'name',
        'email',
        'phone',
        'avatar',
        'status',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => 'boolean',
        ];
    }

    public function getAvatarUrlAttribute(): string
    {
        return $this->avatar
            ? asset('public/uploads/admins/'.$this->avatar)
            : asset('public/uploads/admins/default.png');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'admin_role');
    }

    public function hasRole(string $slug): bool
    {
        return $this->roles->contains('slug', $slug);
    }

    public function hasAnyRole(array $slugs): bool
    {
        return $this->roles->pluck('slug')->intersect($slugs)->isNotEmpty();
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super-admin');
    }

    public function hasPermission(string $slug): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->roles->loadMissing('permissions')
            ->pluck('permissions')
            ->flatten()
            ->contains('slug', $slug);
    }

    /**
     * Admins whose role grants the given permission — used to decide who
     * receives an in-panel/email notification for a given event.
     */
    public function scopeWithPermission(Builder $query, string $slug): Builder
    {
        return $query->whereHas('roles.permissions', fn (Builder $q) => $q->where('slug', $slug));
    }
}
