<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageSection extends Model
{
    public const TYPES = [
        'hero' => 'Hero Banner',
        'rich_text' => 'Rich Text',
        'items' => 'Item Grid (features / services / team / stats)',
        'trust_badges' => 'Trust Badges (licensing, security, availability claims)',
        'faq' => 'FAQ List',
        'contact_info' => 'Contact Info',
        'cta' => 'Call To Action',
        'testimonials' => 'Testimonials (Live Reviews)',
        'fleet' => 'Fleet Showcase (Live Vehicles)',
        'routes' => 'Popular Routes (Live Routes)',
        'areas' => 'Service Areas (Live)',
        'promotions' => 'Promotions (Live)',
        'stats' => 'Statistics Counters',
        'blog' => 'Blog Highlights (Live Posts)',
        'vision_mission' => 'Vision & Mission',
        'team' => 'Team Members',
        'process' => 'Booking Process (Numbered Steps)',
    ];

    protected $fillable = [
        'page_id',
        'type',
        'heading',
        'eyebrow',
        'subheading',
        'differentiator',
        'body',
        'image',
        'video',
        'button_text',
        'button_url',
        'button_text_2',
        'button_url_2',
        'content',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'content' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst(str_replace('_', ' ', $this->type));
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('public/uploads/pages/'.$this->image) : null;
    }

    public function getVideoUrlAttribute(): ?string
    {
        return $this->video ? asset('public/uploads/pages/'.$this->video) : null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getItemsAttribute(): array
    {
        return $this->type === 'items' ? ($this->content ?? []) : [];
    }

    /**
     * The single, shared list of action buttons (Book Now, Call Now / phone
     * number, the Pricing modal trigger, or any admin-defined custom link)
     * used to render BOTH this hero section AND — for the home page's hero
     * specifically, see components/header.blade.php — the site's top
     * navbar. Each button carries independent desktop/mobile visibility for
     * each of those two locations, so e.g. Price can show in the navbar on
     * desktop only while showing in the hero on both. Configured in one
     * place: Admin → Pages → Home → Hero section.
     *
     * A hero section saved before this existed has no 'buttons' key in its
     * content yet, so this falls back to Book Now (from the legacy
     * button_text/button_url columns) + Call Now + Pricing, all shown
     * everywhere — the sensible default this now ships with, and (for
     * button_text/button_url) the same primary CTA that was already there.
     * Once the section is saved again through the admin form, the explicit
     * list here takes over.
     *
     * @return array<int, array{id: string, kind: string, label: ?string, url: ?string, hero_show_desktop: bool, hero_show_mobile: bool, navbar_show_desktop: bool, navbar_show_mobile: bool}>
     */
    public function getHeroButtonsAttribute(): array
    {
        if ($this->type !== 'hero') {
            return [];
        }

        $configured = $this->content['buttons'] ?? null;

        if (is_array($configured)) {
            // Normalizes each entry so both an old save (from before the
            // navbar_show_* fields existed — it only had show_desktop/
            // show_mobile, meaning "in the hero") and a new one read the
            // same shape: an old entry's show_desktop/show_mobile becomes
            // its hero visibility, and its navbar visibility (a concept
            // that didn't exist yet) defaults to shown everywhere.
            return collect($configured)->map(fn ($button) => [
                'id' => $button['id'] ?? null,
                'kind' => $button['kind'] ?? 'custom',
                'label' => $button['label'] ?? null,
                'url' => $button['url'] ?? null,
                'hero_show_desktop' => $button['hero_show_desktop'] ?? $button['show_desktop'] ?? true,
                'hero_show_mobile' => $button['hero_show_mobile'] ?? $button['show_mobile'] ?? true,
                'navbar_show_desktop' => $button['navbar_show_desktop'] ?? true,
                'navbar_show_mobile' => $button['navbar_show_mobile'] ?? true,
                'bg_color' => $button['bg_color'] ?? null,
                'text_color' => $button['text_color'] ?? null,
            ])->all();
        }

        $buttons = [];

        $buttons[] = [
            'id' => 'legacy-primary',
            'kind' => 'book_now',
            'label' => $this->button_text ?: 'Book Now',
            'url' => $this->button_url ?: '/#booking-widget',
            'hero_show_desktop' => true, 'hero_show_mobile' => true,
            'navbar_show_desktop' => true, 'navbar_show_mobile' => true,
        ];

        if ($this->button_text_2 && $this->button_url_2) {
            $buttons[] = [
                'id' => 'legacy-secondary',
                'kind' => 'custom',
                'label' => $this->button_text_2,
                'url' => $this->button_url_2,
                'hero_show_desktop' => true, 'hero_show_mobile' => true,
                'navbar_show_desktop' => false, 'navbar_show_mobile' => false,
            ];
        }

        $buttons[] = [
            'id' => 'legacy-call', 'kind' => 'call_now', 'label' => 'Call Now', 'url' => null,
            'hero_show_desktop' => true, 'hero_show_mobile' => true,
            'navbar_show_desktop' => true, 'navbar_show_mobile' => true,
        ];

        $buttons[] = [
            'id' => 'legacy-price', 'kind' => 'price', 'label' => 'Pricing', 'url' => null,
            'hero_show_desktop' => true, 'hero_show_mobile' => true,
            'navbar_show_desktop' => true, 'navbar_show_mobile' => true,
        ];

        return $buttons;
    }

    /**
     * @return array<int, array{icon: ?string, title: string, description: ?string, link: ?string}>
     */
    public function getTrustBadgeItemsAttribute(): array
    {
        return $this->type === 'trust_badges' ? ($this->content ?? []) : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getFaqItemsAttribute(): array
    {
        return $this->type === 'faq' ? ($this->content ?? []) : [];
    }

    /**
     * @return array<string, mixed>
     */
    public function getContactFieldsAttribute(): array
    {
        return $this->type === 'contact_info' ? ($this->content ?? []) : [];
    }

    /**
     * @return array{limit: int, min_rating: int}
     */
    public function getTestimonialSettingsAttribute(): array
    {
        $content = $this->type === 'testimonials' ? ($this->content ?? []) : [];

        return [
            'limit' => (int) ($content['limit'] ?? 6),
            'min_rating' => (int) ($content['min_rating'] ?? 4),
        ];
    }

    /**
     * @return array{limit: int, category_id: ?int}
     */
    public function getFleetSettingsAttribute(): array
    {
        $content = $this->type === 'fleet' ? ($this->content ?? []) : [];

        return [
            'limit' => (int) ($content['limit'] ?? 12),
            'category_id' => isset($content['category_id']) ? (int) $content['category_id'] : null,
        ];
    }

    /**
     * @return array{limit: int}
     */
    public function getRouteSettingsAttribute(): array
    {
        $content = $this->type === 'routes' ? ($this->content ?? []) : [];

        return [
            'limit' => (int) ($content['limit'] ?? 6),
        ];
    }

    /**
     * @return array<int, array{icon: ?string, label: string, value: int, suffix: ?string}>
     */
    public function getStatsAttribute(): array
    {
        return $this->type === 'stats' ? ($this->content ?? []) : [];
    }

    /**
     * @return array{limit: int}
     */
    public function getBlogSettingsAttribute(): array
    {
        $content = $this->type === 'blog' ? ($this->content ?? []) : [];

        return [
            'limit' => (int) ($content['limit'] ?? 6),
        ];
    }

    /**
     * @return array{vision_icon: ?string, vision_title: ?string, vision_body: ?string, mission_icon: ?string, mission_title: ?string, mission_body: ?string}
     */
    public function getVisionMissionAttribute(): array
    {
        $content = $this->type === 'vision_mission' ? ($this->content ?? []) : [];

        return [
            'vision_icon' => $content['vision_icon'] ?? 'eye',
            'vision_title' => $content['vision_title'] ?? 'Our Vision',
            'vision_body' => $content['vision_body'] ?? null,
            'mission_icon' => $content['mission_icon'] ?? 'trending-up',
            'mission_title' => $content['mission_title'] ?? 'Our Mission',
            'mission_body' => $content['mission_body'] ?? null,
        ];
    }

    /**
     * @return array<int, array{photo: ?string, name: string, role: ?string, bio: ?string}>
     */
    public function getTeamMembersAttribute(): array
    {
        return $this->type === 'team' ? ($this->content ?? []) : [];
    }

    /**
     * @return array<int, array{icon: ?string, title: string, description: ?string}>
     */
    public function getProcessStepsAttribute(): array
    {
        return $this->type === 'process' ? ($this->content ?? []) : [];
    }
}
