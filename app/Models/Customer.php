<?php

namespace App\Models;

use App\Models\Concerns\HasPushSubscriptions;
use App\Notifications\Customer\ResetPassword;
use App\Notifications\Customer\VerifyEmail;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class Customer extends Authenticatable implements MustVerifyEmailContract
{
    use HasFactory, HasPushSubscriptions, MustVerifyEmailTrait, Notifiable;

    public const GENDERS = [
        'male' => 'Male',
        'female' => 'Female',
        'other' => 'Other',
        'prefer_not_to_say' => 'Prefer not to say',
    ];

    /**
     * Keeps the single `name` column (read throughout the admin panel,
     * invoices, testimonials, etc.) in sync whenever first/last name are
     * set, so the granular profile fields stay backward-compatible with
     * every existing "$customer->name" usage.
     */
    protected static function booted(): void
    {
        static::saving(function (self $customer) {
            if ($customer->isDirty('first_name') || $customer->isDirty('last_name')) {
                $customer->name = trim("{$customer->first_name} {$customer->last_name}") ?: $customer->name;
            }
        });
    }

    /**
     * Laravel's default notification hardcodes a route name that doesn't
     * exist under the customer guard's prefixed routes — see the custom
     * notification class for details.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmail);
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPassword($token));
    }

    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'phone',
        'date_of_birth',
        'gender',
        'avatar',
        'status',
        'password',
        'wallet_balance',
        'loyalty_points',
        'locale',
        'currency',
        'theme_mode',
        'email_notifications_enabled',
        'push_notifications_enabled',
        'sms_notifications_enabled',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'password' => 'hashed',
            'email_verified_at' => 'datetime',
            'date_of_birth' => 'date',
            'wallet_balance' => 'decimal:2',
            'loyalty_points' => 'integer',
            'email_notifications_enabled' => 'boolean',
            'push_notifications_enabled' => 'boolean',
            'sms_notifications_enabled' => 'boolean',
        ];
    }

    /**
     * Applies this customer's saved locale/currency/theme preferences to the
     * current session, so they take effect immediately on login — on any
     * device — without waiting for the customer to revisit Settings.
     */
    public function applyPreferencesToSession(): void
    {
        if ($this->locale) {
            session(['locale' => $this->locale]);
        }

        if ($this->currency) {
            session(['currency' => $this->currency]);
        }

        if ($this->theme_mode) {
            session(['customer_theme_mode' => $this->theme_mode]);
        }
    }

    public function getGenderLabelAttribute(): ?string
    {
        return $this->gender ? (self::GENDERS[$this->gender] ?? ucfirst($this->gender)) : null;
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class)->orderByDesc('is_default');
    }

    public function walletTransactions(): HasMany
    {
        return $this->hasMany(CustomerWalletTransaction::class)->latest();
    }

    public function loyaltyTransactions(): HasMany
    {
        return $this->hasMany(CustomerLoyaltyTransaction::class)->latest();
    }

    public function loginHistories(): HasMany
    {
        return $this->hasMany(CustomerLoginHistory::class)->latest('created_at');
    }

    public function activeSessions(): HasMany
    {
        return $this->hasMany(CustomerActiveSession::class)->latest('created_at');
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class)->latest();
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->latest();
    }

    public function favoriteVehicles(): BelongsToMany
    {
        return $this->belongsToMany(Vehicle::class, 'customer_favorites')->withTimestamps();
    }

    public function hasFavorited(Vehicle $vehicle): bool
    {
        return $this->favoriteVehicles()->where('vehicles.id', $vehicle->id)->exists();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar ? asset('public/uploads/customers/'.$this->avatar) : null;
    }

    public function creditWallet(float $amount, ?string $reason, ?int $adminId = null): CustomerWalletTransaction
    {
        return DB::transaction(function () use ($amount, $reason, $adminId) {
            $this->increment('wallet_balance', $amount);

            return $this->walletTransactions()->create([
                'type' => 'credit',
                'amount' => $amount,
                'balance_after' => $this->fresh()->wallet_balance,
                'reason' => $reason,
                'admin_id' => $adminId,
            ]);
        });
    }

    public function debitWallet(float $amount, ?string $reason, ?int $adminId = null): CustomerWalletTransaction
    {
        return DB::transaction(function () use ($amount, $reason, $adminId) {
            $this->decrement('wallet_balance', $amount);

            return $this->walletTransactions()->create([
                'type' => 'debit',
                'amount' => $amount,
                'balance_after' => $this->fresh()->wallet_balance,
                'reason' => $reason,
                'admin_id' => $adminId,
            ]);
        });
    }

    public function earnLoyaltyPoints(int $points, ?string $reason, ?int $adminId = null): CustomerLoyaltyTransaction
    {
        return DB::transaction(function () use ($points, $reason, $adminId) {
            $this->increment('loyalty_points', $points);

            return $this->loyaltyTransactions()->create([
                'type' => 'earned',
                'points' => $points,
                'balance_after' => $this->fresh()->loyalty_points,
                'reason' => $reason,
                'admin_id' => $adminId,
            ]);
        });
    }

    public function redeemLoyaltyPoints(int $points, ?string $reason, ?int $adminId = null): CustomerLoyaltyTransaction
    {
        return DB::transaction(function () use ($points, $reason, $adminId) {
            $this->decrement('loyalty_points', $points);

            return $this->loyaltyTransactions()->create([
                'type' => 'redeemed',
                'points' => $points,
                'balance_after' => $this->fresh()->loyalty_points,
                'reason' => $reason,
                'admin_id' => $adminId,
            ]);
        });
    }
}
