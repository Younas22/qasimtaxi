<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Redirect extends Model
{
    public const TYPES = [
        301 => '301 (Permanent)',
        302 => '302 (Temporary)',
    ];

    protected $fillable = [
        'old_path',
        'new_path',
        'type',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $redirect) {
            $redirect->old_path = self::normalizePath($redirect->old_path);
            $redirect->new_path = self::normalizePath($redirect->new_path);
        });
    }

    /**
     * Strips leading/trailing slashes and the domain if one was pasted in,
     * so "/old-page/", "old-page", and "https://example.com/old-page" all
     * resolve to the same lookup key.
     */
    public static function normalizePath(?string $path): string
    {
        $path = (string) $path;

        if (Str::startsWith($path, ['http://', 'https://'])) {
            $path = (string) parse_url($path, PHP_URL_PATH);
        }

        return trim($path, '/');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Looks up an active redirect for the given request path (without
     * leading/trailing slashes) — used by the catch-all fallback route.
     */
    public static function findFor(string $path): ?self
    {
        return self::active()->where('old_path', self::normalizePath($path))->first();
    }
}
