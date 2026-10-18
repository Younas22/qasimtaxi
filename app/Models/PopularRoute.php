<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PopularRoute extends Model
{
    protected $fillable = [
        'route_type_id',
        'pickup',
        'dropoff',
        'distance',
        'distance_unit',
        'estimated_price',
        'original_price',
        'image',
        'hover_image',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'distance' => 'decimal:2',
            'estimated_price' => 'decimal:2',
            'original_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * True only when there's a genuine discount to show — a blank or
     * not-actually-higher "original" price silently falls back to the
     * normal single-price display instead of showing a nonsensical or
     * missing strikethrough.
     */
    public function getHasDiscountAttribute(): bool
    {
        return $this->original_price !== null
            && $this->estimated_price !== null
            && (float) $this->original_price > (float) $this->estimated_price;
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('public/uploads/popular-routes/'.$this->image) : null;
    }

    public function getHoverImageUrlAttribute(): ?string
    {
        return $this->hover_image ? asset('public/uploads/popular-routes/'.$this->hover_image) : null;
    }

    public function routeType(): BelongsTo
    {
        return $this->belongsTo(RouteType::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType(Builder $query, int $routeTypeId): Builder
    {
        return $query->where('route_type_id', $routeTypeId);
    }
}
