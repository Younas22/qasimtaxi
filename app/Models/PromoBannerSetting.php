<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Single-row settings singleton controlling the two vendor-only "this
 * software is for sale" promo elements — the modal (software-sale-modal
 * .blade.php) and the sticky bottom-corner banner (software-sale-sticky-
 * banner.blade.php). Both only ever render on the sale/demo domain to begin
 * with; these two switches let the vendor additionally pick which one (or
 * both, or neither) is actually showing, from a private, unlinked page —
 * see PromoBannerSettingController.
 */
class PromoBannerSetting extends Model
{
    public const CACHE_KEY = 'promo_banner.settings';

    protected $fillable = [
        'sale_modal_enabled',
        'sticky_banner_enabled',
    ];

    protected function casts(): array
    {
        return [
            'sale_modal_enabled' => 'boolean',
            'sticky_banner_enabled' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    public static function current(): self
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            // Passed explicitly rather than left to the migration's column
            // defaults — firstOrCreate()'s in-memory model only knows the
            // attributes it was given; it never re-reads back whatever a
            // DB-level default filled in after the INSERT. Relying on the
            // column default here left the very model instance that gets
            // cached (forever) missing both booleans entirely, so both
            // checkboxes rendered unchecked despite the row's real values
            // being true.
            return self::firstOrCreate(['id' => 1], [
                'sale_modal_enabled' => true,
                'sticky_banner_enabled' => true,
            ]);
        });
    }
}
