<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class VehicleCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'image',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $category) {
            $category->slug ??= $category->uniqueSlug($category->name);

            if (is_null($category->sort_order) || $category->sort_order === 0) {
                $category->sort_order = (static::max('sort_order') ?? 0) + 1;
            }
        });
    }

    public function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;

        while (static::where('slug', $slug)->when($this->exists, fn ($q) => $q->where('id', '!=', $this->id))->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    public function pricingRule(): HasOne
    {
        return $this->hasOne(PricingRule::class, 'vehicle_category_id');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getIconUrlAttribute(): ?string
    {
        return $this->icon ? asset('public/uploads/vehicle-categories/'.$this->icon) : null;
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('public/uploads/vehicle-categories/'.$this->image) : null;
    }
}
