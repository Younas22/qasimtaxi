<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    public const DISCOUNT_TYPES = [
        'percentage' => 'Percentage',
        'fixed' => 'Fixed Amount',
    ];

    protected $fillable = [
        'code',
        'description',
        'discount_type',
        'discount_value',
        'min_fare',
        'max_discount',
        'usage_limit',
        'used_count',
        'expires_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'min_fare' => 'decimal:2',
            'max_discount' => 'decimal:2',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()))
            ->where(fn ($q) => $q->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit'));
    }

    public function getDiscountLabelAttribute(): string
    {
        return $this->discount_type === 'percentage'
            ? "{$this->formattedDiscountValue()}% off"
            : currency($this->discount_value).' off';
    }

    protected function formattedDiscountValue(): string
    {
        return rtrim(rtrim(number_format((float) $this->discount_value, 2), '0'), '.');
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isExhausted(): bool
    {
        return $this->usage_limit !== null && $this->used_count >= $this->usage_limit;
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Everything that can make a coupon unusable right now, checked in the
     * order a customer would want to hear about them — reused as-is by both
     * the live widget preview and the authoritative check at booking time,
     * so the two can never disagree about whether a code works.
     */
    public function ineligibilityReason(float $fareAmount): ?string
    {
        if (! $this->is_active) {
            return __('This coupon is no longer active.');
        }

        if ($this->isExpired()) {
            return __('This coupon has expired.');
        }

        if ($this->isExhausted()) {
            return __('This coupon has reached its usage limit.');
        }

        if ($this->min_fare !== null && $fareAmount < (float) $this->min_fare) {
            return __('This coupon requires a minimum fare of :amount.', ['amount' => currency($this->min_fare)]);
        }

        return null;
    }

    public function isValidFor(float $fareAmount): bool
    {
        return $this->ineligibilityReason($fareAmount) === null;
    }

    /**
     * The actual amount to knock off a given fare — percentage or fixed,
     * capped by max_discount when set, and never past the fare itself
     * (a coupon can make a ride free, never negative).
     */
    public function discountFor(float $fareAmount): float
    {
        $discount = $this->discount_type === 'percentage'
            ? $fareAmount * ((float) $this->discount_value / 100)
            : (float) $this->discount_value;

        if ($this->max_discount !== null) {
            $discount = min($discount, (float) $this->max_discount);
        }

        return round(min($discount, $fareAmount), 2);
    }
}
