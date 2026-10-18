<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Area extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'meta_title',
        'meta_description',
        'og_image',
        'canonical_override',
        'robots_index',
        'robots_follow',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'robots_index' => 'boolean',
            'robots_follow' => 'boolean',
        ];
    }

    public function getOgImageUrlAttribute(): ?string
    {
        return $this->og_image ? asset('public/uploads/areas/'.$this->og_image) : null;
    }

    protected static function booted(): void
    {
        static::creating(function (self $area) {
            $area->slug ??= $area->uniqueSlug($area->name);

            if (is_null($area->sort_order) || $area->sort_order === 0) {
                $area->sort_order = (static::max('sort_order') ?? 0) + 1;
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

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
