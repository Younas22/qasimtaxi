<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Currency extends Model
{
    public const CACHE_ACTIVE_KEY = 'currencies.active';

    public const CACHE_DEFAULT_KEY = 'currencies.default';

    protected $fillable = [
        'name',
        'code',
        'symbol',
        'exchange_rate',
        'is_default',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'exchange_rate' => 'decimal:6',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => self::flushCache());
        static::deleted(fn () => self::flushCache());
    }

    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_ACTIVE_KEY);
        Cache::forget(self::CACHE_DEFAULT_KEY);
    }

    /**
     * All enabled currencies, cached.
     */
    public static function active(): \Illuminate\Support\Collection
    {
        return Cache::rememberForever(self::CACHE_ACTIVE_KEY, function () {
            return self::where('is_active', true)->orderBy('code')->get();
        });
    }

    public static function default(): ?self
    {
        return Cache::rememberForever(self::CACHE_DEFAULT_KEY, function () {
            return self::where('is_default', true)->first() ?? self::orderBy('id')->first();
        });
    }

    public static function defaultCode(): string
    {
        return self::default()?->code ?? 'USD';
    }

    public static function findActiveByCode(string $code): ?self
    {
        return self::active()->firstWhere('code', $code);
    }

    /**
     * Convert an amount stored in the default currency's base units into this currency.
     */
    public function convert(float|string $amount): float
    {
        return round(((float) $amount) * (float) $this->exchange_rate, 2);
    }

    /**
     * Convert and format an amount stored in the default currency's base units.
     */
    public function format(float|string $amount): string
    {
        return $this->symbol.number_format($this->convert($amount), 2);
    }

    /**
     * A colored country-flag emoji derived from the ISO 4217 code, e.g. USD -> US -> 🇺🇸.
     * Windows doesn't ship flag glyphs in its emoji font, so prefer
     * flag_country_code (rendered via the flag-icons CSS library) in the UI.
     */
    public function getFlagEmojiAttribute(): string
    {
        return collect(str_split($this->flag_country_code))
            ->map(fn ($char) => mb_chr(127397 + ord(strtoupper($char))))
            ->implode('');
    }

    /**
     * Lowercase ISO 3166-1 country code derived from the ISO 4217 currency
     * code, for use with the flag-icons CSS library (e.g. USD -> us).
     */
    public function getFlagCountryCodeAttribute(): string
    {
        return strtoupper($this->code) === 'EUR' ? 'eu' : strtolower(substr($this->code, 0, 2));
    }
}
