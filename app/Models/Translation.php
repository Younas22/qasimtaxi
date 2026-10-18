<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class Translation extends Model
{
    protected $fillable = [
        'language_id',
        'group',
        'key',
        'value',
    ];

    protected static function booted(): void
    {
        static::saved(fn (self $translation) => $translation->flushCache());
        static::deleted(fn (self $translation) => $translation->flushCache());
    }

    public function flushCache(): void
    {
        $code = $this->relationLoaded('language')
            ? $this->language?->code
            : Language::where('id', $this->language_id)->value('code');

        if ($code) {
            Cache::forget("translations.{$code}.{$this->group}");
        }
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    /**
     * All distinct translation keys known across every language, for a group.
     *
     * @return \Illuminate\Support\Collection<int, string>
     */
    public static function knownKeys(string $group = '*'): \Illuminate\Support\Collection
    {
        return static::where('group', $group)
            ->select('key')
            ->distinct()
            ->orderBy('key')
            ->pluck('key');
    }
}
