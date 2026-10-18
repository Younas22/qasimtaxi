<?php

namespace App\Models;

use App\Models\Concerns\HasPushSubscriptions;
use App\Notifications\Driver\ResetPassword;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Driver extends Authenticatable
{
    use HasFactory, HasPushSubscriptions, Notifiable;

    protected $fillable = [
        'vehicle_id',
        'name',
        'email',
        'phone',
        'address',
        'license_number',
        'passport_number',
        'national_id',
        'photo',
        'status',
        'commission_rate',
        'is_online',
        'is_available',
        'password',
        'locale',
        'theme_mode',
        'current_lat',
        'current_lng',
        'location_updated_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'is_online' => 'boolean',
            'is_available' => 'boolean',
            'commission_rate' => 'decimal:2',
            'password' => 'hashed',
            'current_lat' => 'decimal:7',
            'current_lng' => 'decimal:7',
            'location_updated_at' => 'datetime',
        ];
    }

    /**
     * A driver's GPS ping is only trusted as "live" for a short window —
     * beyond this, dispatch logic falls back to the office as the dispatch
     * point rather than showing a stale position as if it were current.
     */
    public function hasFreshLocation(): bool
    {
        return $this->current_lat !== null
            && $this->current_lng !== null
            && $this->location_updated_at !== null
            && $this->location_updated_at->gt(now()->subMinutes(5));
    }

    /**
     * The booking this driver is actively driving right now, if any.
     */
    public function activeBooking(): ?Booking
    {
        return $this->bookings()->where('status', 'in_progress')->first();
    }

    /**
     * Laravel's default notification hardcodes a route name that doesn't
     * exist under the driver guard's prefixed routes — see the custom
     * notification class for details.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPassword($token));
    }

    /**
     * Applies this driver's saved locale/theme preferences to the current
     * session, so they take effect immediately on login — matches the same
     * pattern used for customers.
     */
    public function applyPreferencesToSession(): void
    {
        if ($this->locale) {
            session(['locale' => $this->locale]);
        }

        if ($this->theme_mode) {
            session(['driver_theme_mode' => $this->theme_mode]);
        }
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->latest();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo ? asset('public/uploads/drivers/'.$this->photo) : null;
    }

    public function getAverageRatingAttribute(): ?float
    {
        $average = $this->reviews()->approved()->avg('rating');

        return $average ? round((float) $average, 1) : null;
    }
}
