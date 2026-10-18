<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
{
    public const PAGES = [
        'home' => 'Home',
        'about' => 'About',
        'services' => 'Services',
        'areas' => 'Areas',
        'faq' => 'FAQ',
        'contact' => 'Contact',
        'airport-transfer' => 'Airport Transfer',
        'chauffeur-service' => 'Chauffeur Service',
        'corporate-transfer' => 'Corporate Transfer',
        'city-rides' => 'City Rides',
        'hourly-rides' => 'Hourly Rides',
        'vip-transport' => 'VIP Transport',
        'privacy-policy' => 'Privacy Policy',
        'terms' => 'Terms & Conditions',
        'refund-policy' => 'Refund Policy',
        'cookie-policy' => 'Cookie Policy',
        'taxi-maastricht-brussels-airport' => 'Taxi naar Maastricht Aachen Airport & Brussels Airport',
    ];

    /**
     * Dedicated SEO landing pages — rendered exactly like a service page
     * (compact banner + booking widget, "Services" breadcrumb) but kept out
     * of the header/sidebar/footer menus so they're only reached through
     * contextual links, never a sitewide exact-match keyword link.
     */
    public const LANDING_PAGES = [
        'taxi-maastricht-brussels-airport',
    ];

    /**
     * Slugs for the dedicated service pages, each rendered with a
     * banner/description/fleet/CTA layout and linked from the Services
     * overview page rather than the primary site navigation.
     */
    public const SERVICE_PAGES = [
        'airport-transfer',
        'chauffeur-service',
        'corporate-transfer',
        'city-rides',
        'hourly-rides',
        'vip-transport',
    ];

    /**
     * Legal/compliance pages linked from the footer's "Legal" column
     * rather than the primary site navigation.
     */
    public const LEGAL_PAGES = [
        'privacy-policy',
        'terms',
        'refund-policy',
        'cookie-policy',
    ];

    /**
     * Pages that use the service-page layout: the six SERVICE_PAGES plus
     * any LANDING_PAGES.
     */
    public static function isServiceLike(string $slug): bool
    {
        return in_array($slug, self::SERVICE_PAGES, true) || in_array($slug, self::LANDING_PAGES, true);
    }

    /**
     * Pages never listed in the primary navigation menus.
     */
    public static function isExcludedFromNav(string $slug): bool
    {
        return in_array($slug, self::LEGAL_PAGES, true) || self::isServiceLike($slug);
    }

    protected $fillable = [
        'slug',
        'name',
        'meta_title',
        'meta_description',
        'og_image',
        'canonical_override',
        'robots_index',
        'robots_follow',
        'custom_schema',
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
        return $this->og_image ? asset('public/uploads/pages/'.$this->og_image) : null;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function sections(): HasMany
    {
        return $this->hasMany(PageSection::class)->orderBy('sort_order');
    }

    public function activeSections(): HasMany
    {
        return $this->sections()->where('is_active', true);
    }
}
