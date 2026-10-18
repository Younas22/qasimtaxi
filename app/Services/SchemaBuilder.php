<?php

namespace App\Services;

use App\Models\Area;
use App\Models\Setting;

/**
 * Assembles JSON-LD structured-data blocks purely from data the admin has
 * already entered (Settings, active Areas, a page's own FAQ content) —
 * nothing here is invented or hard-coded per customer. Kept as a plain PHP
 * service (not written inline in a .blade.php file) because Blade's
 * compiler rewrites "@word" sequences as directives even inside embedded
 * PHP/JSON, which corrupts literal "@context"/"@type" array keys — see the
 * same note on organization_schema() in app/helpers.php, which this class
 * builds on rather than duplicates.
 */
class SchemaBuilder
{
    /**
     * The subset of Setting::BUSINESS_TYPES that map onto a real,
     * unambiguous schema.org LocalBusiness subtype. Anything else (limo,
     * chauffeur, black car, "other"...) has no matching schema.org type, so
     * it stays a plain LocalBusiness rather than inventing one.
     */
    private const SCHEMA_TYPE_MAP = [
        'taxi' => 'TaxiService',
        'airport_transfer' => 'TaxiService',
    ];

    /**
     * The site-wide Organization/LocalBusiness block — present on every
     * page. Extends organization_schema()'s base fields with the business
     * type, geo coordinates, opening hours, and service areas, all sourced
     * from data the admin already maintains in Settings/Areas.
     *
     * @return array<string, mixed>
     */
    public function organization(): array
    {
        $settings = Setting::current();
        $schema = organization_schema();

        $schema['@type'] = self::SCHEMA_TYPE_MAP[$settings->business_type] ?? 'LocalBusiness';
        $schema = ['@context' => $schema['@context'], '@type' => $schema['@type'], '@id' => $this->organizationId()] + $schema;

        if ($settings->tagline) {
            $schema['description'] = __($settings->tagline);
        }

        if ($address = $this->postalAddress($settings)) {
            $schema['address'] = $address;
        }

        if ($settings->office_lat && $settings->office_lng) {
            $schema['geo'] = [
                '@type' => 'GeoCoordinates',
                'latitude' => (float) $settings->office_lat,
                'longitude' => (float) $settings->office_lng,
            ];
        }

        $hours = $this->openingHours($settings);
        if ($hours !== []) {
            $schema['openingHoursSpecification'] = $hours;
        }

        $areas = Area::active()->ordered()->pluck('name');
        if ($areas->isNotEmpty()) {
            $schema['areaServed'] = $areas->values()->all();
        }

        return $schema;
    }

    /**
     * Stable identifier for the site-wide business entity, so page-level
     * blocks (WebPage, Service, BlogPosting) can reference it instead of
     * repeating a second, possibly conflicting, copy of the business.
     */
    public function organizationId(): string
    {
        // Same "/#business" id the admin-authored custom schema on the
        // service pages already uses, so both describe one entity.
        return rtrim(url('/'), '/').'/#business';
    }

    public function websiteId(): string
    {
        return rtrim(url('/'), '/').'/#website';
    }

    /**
     * Splits the single admin-entered address line ("Street 1, 3740 City,
     * Country") into PostalAddress parts when it follows that common
     * Belgian/Dutch format; anything else stays a plain streetAddress
     * rather than guessing.
     *
     * @return array<string, string>|null
     */
    private function postalAddress(Setting $settings): ?array
    {
        $address = trim((string) $settings->address);

        if ($address === '') {
            return null;
        }

        if (preg_match('/^(?<street>.+?),\s*(?<postal>\d{4,5}(?:\s?[A-Z]{2})?)\s+(?<city>[^,]+?)(?:,\s*(?<country>[^,]+))?$/u', $address, $m)) {
            return array_filter([
                '@type' => 'PostalAddress',
                'streetAddress' => trim($m['street']),
                'postalCode' => trim($m['postal']),
                'addressLocality' => trim($m['city']),
                'addressCountry' => $settings->default_country ?: (isset($m['country']) ? trim($m['country']) : null),
            ]);
        }

        return ['@type' => 'PostalAddress', 'streetAddress' => $address];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function openingHours(Setting $settings): array
    {
        $days = [
            'monday' => 'Monday', 'tuesday' => 'Tuesday', 'wednesday' => 'Wednesday',
            'thursday' => 'Thursday', 'friday' => 'Friday', 'saturday' => 'Saturday', 'sunday' => 'Sunday',
        ];

        $specs = [];

        foreach ($settings->business_hours_list as $day => $hours) {
            if (! empty($hours['closed'])) {
                continue;
            }

            // Identical open/close times (e.g. 00:00–00:00) is how the
            // admin form expresses "open the whole day" — schema.org reads
            // 00:00–00:00 as zero opening time, so spell out the full day.
            $allDay = $hours['open'] === $hours['close'];

            $specs[] = [
                '@type' => 'OpeningHoursSpecification',
                'dayOfWeek' => 'https://schema.org/'.$days[$day],
                'opens' => $allDay ? '00:00' : $hours['open'],
                'closes' => $allDay ? '23:59' : $hours['close'],
            ];
        }

        return $specs;
    }

    /**
     * @return array<string, mixed>
     */
    public function website(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            '@id' => $this->websiteId(),
            'name' => setting('company_name'),
            'url' => url('/'),
            'inLanguage' => $this->language(),
            'publisher' => ['@id' => $this->organizationId()],
        ];
    }

    /**
     * BCP 47 language of the rendered page — Belgian Dutch for the
     * site's primary "nl" locale.
     */
    public function language(): string
    {
        $locale = str_replace('_', '-', app()->getLocale());

        return $locale === 'nl' ? 'nl-BE' : $locale;
    }

    /**
     * @return array<string, mixed>
     */
    public function webPage(string $name, ?string $description = null, ?string $canonicalOverride = null): array
    {
        $url = $canonicalOverride ?: url()->current();

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            '@id' => $url.'#webpage',
            'url' => $url,
            'name' => seo_title($name),
            'description' => $description,
            'inLanguage' => $this->language(),
            'isPartOf' => ['@id' => $this->websiteId()],
            'about' => ['@id' => $this->organizationId()],
        ]);
    }

    /**
     * A service the business itself offers on this page, provided by the
     * site-wide business entity (referenced by @id, not duplicated).
     *
     * @return array<string, mixed>
     */
    public function service(string $name, ?string $description = null, ?string $canonicalOverride = null): array
    {
        $url = $canonicalOverride ?: url()->current();
        $settings = Setting::current();

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            '@id' => $url.'#service',
            'name' => $name,
            'description' => $description,
            'serviceType' => 'Taxivervoer',
            'url' => $url,
            'provider' => ['@id' => $this->organizationId()],
            'areaServed' => $this->postalAddress($settings)['addressLocality'] ?? null,
            'availableChannel' => array_filter([
                '@type' => 'ServiceChannel',
                'serviceUrl' => $url,
                'servicePhone' => $settings->phone ? ['@type' => 'ContactPoint', 'telephone' => $settings->phone, 'contactType' => 'reservations'] : null,
            ]),
        ]);
    }

    /**
     * @param  array<int, array{question?: string, answer?: string}>  $items
     * @return array<string, mixed>|null
     */
    public function faqPage(array $items): ?array
    {
        $entities = collect($items)
            ->filter(fn ($item) => filled($item['question'] ?? null) && filled($item['answer'] ?? null))
            ->map(fn ($item) => [
                '@type' => 'Question',
                // Same __() pass the visible accordion applies, so the
                // schema always matches the text a visitor actually reads.
                'name' => strip_tags(__($item['question'])),
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => strip_tags(__($item['answer'])),
                ],
            ])
            ->values();

        if ($entities->isEmpty()) {
            return null;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $entities->all(),
        ];
    }

    /**
     * @param  array<int, array{label: string, url: ?string}>  $items
     * @return array<string, mixed>
     */
    public function breadcrumbList(array $items): array
    {
        $listItems = collect($items)->values()->map(fn ($item, $index) => array_filter([
            '@type' => 'ListItem',
            'position' => $index + 1,
            'name' => $item['label'],
            'item' => $item['url'] ?? null,
        ]))->all();

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $listItems,
        ];
    }
}
