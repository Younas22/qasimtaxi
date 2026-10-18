<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Tag extends Model
{
    protected $fillable = [
        'name',
        'slug',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $tag) {
            $tag->slug ??= $tag->uniqueSlug($tag->name);
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

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(BlogPost::class);
    }

    /**
     * Find-or-create a batch of Tag models from raw name strings, used by
     * the post form's free-text tag input.
     *
     * @param  array<int, string>  $names
     * @return \Illuminate\Support\Collection<int, self>
     */
    public static function resolveByNames(array $names): \Illuminate\Support\Collection
    {
        return collect($names)
            ->map(fn ($name) => trim($name))
            ->filter()
            ->unique(fn ($name) => Str::lower($name))
            ->map(fn ($name) => static::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            ));
    }
}
