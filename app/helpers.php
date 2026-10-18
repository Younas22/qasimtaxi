<?php

use App\Models\BookingSetting;
use App\Models\Currency;
use App\Models\PaymentGateway;
use App\Models\Setting;

if (! function_exists('setting')) {
    /**
     * Access the global site settings singleton, or a single attribute from it.
     *
     * @param  string|null  $key
     * @param  mixed  $default
     * @return mixed
     */
    function setting(?string $key = null, mixed $default = null): mixed
    {
        $settings = Setting::current();

        if (is_null($key)) {
            return $settings;
        }

        return $settings->{$key} ?? $default;
    }
}

if (! function_exists('booking_setting')) {
    /**
     * Access the booking settings singleton, or a single attribute from it.
     *
     * @param  string|null  $key
     * @param  mixed  $default
     * @return mixed
     */
    function booking_setting(?string $key = null, mixed $default = null): mixed
    {
        $settings = BookingSetting::current();

        if (is_null($key)) {
            return $settings;
        }

        return $settings->{$key} ?? $default;
    }
}

if (! function_exists('payment_gateway')) {
    /**
     * Look up a payment gateway by its code (e.g. "stripe", "paypal").
     */
    function payment_gateway(string $code): ?PaymentGateway
    {
        return PaymentGateway::where('code', $code)->first();
    }
}

if (! function_exists('active_currency')) {
    /**
     * The currently selected currency (session preference, falling back to default).
     */
    function active_currency(): ?Currency
    {
        $currencies = Currency::active();

        $code = session('currency');

        return ($code ? $currencies->firstWhere('code', $code) : null)
            ?? Currency::default()
            ?? $currencies->first();
    }
}

if (! function_exists('currency')) {
    /**
     * Format an amount (stored in the default currency's base units) using the
     * active currency. Called without arguments, returns the active Currency model.
     */
    function currency(float|string|null $amount = null): mixed
    {
        $current = active_currency();

        if (is_null($amount)) {
            return $current;
        }

        return $current ? $current->format($amount) : number_format((float) $amount, 2);
    }
}

if (! function_exists('seo_title')) {
    /**
     * Builds a <title>/OG-title from the site's configurable template
     * (Setting::seo_title_template, e.g. "{page_title} | {business_name}"),
     * guarding against the business name being appended twice when the page
     * title already contains it (e.g. an admin-written title of "Airport
     * Transfer | Qasim Taxi" would otherwise become "... | Qasim Taxi —
     * Qasim Taxi").
     */
    function seo_title(string $pageTitle): string
    {
        $businessName = (string) setting('company_name', config('app.name', 'Limo Schedule'));

        if ($businessName !== '' && str_contains(mb_strtolower($pageTitle), mb_strtolower($businessName))) {
            return $pageTitle;
        }

        $template = (string) setting('seo_title_template') ?: '{page_title} | {business_name}';

        return strtr($template, [
            '{page_title}' => $pageTitle,
            '{business_name}' => $businessName,
        ]);
    }
}

if (! function_exists('organization_schema')) {
    /**
     * Site-wide Organization structured data (JSON-LD), built here rather than
     * inline in a .blade.php file — Blade's compiler textually rewrites `@word`
     * sequences as directives even inside embedded PHP/JSON, which corrupts
     * literal "@context"/"@type" array keys if written directly in a view.
     *
     * @return array<string, mixed>
     */
    function organization_schema(): array
    {
        $settings = Setting::current();

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $settings->company_name,
            'url' => url('/'),
        ];

        if ($settings->logo_url) {
            $schema['logo'] = $settings->logo_url;
        }

        if ($settings->phone) {
            $schema['telephone'] = $settings->phone;
        }

        if ($settings->email) {
            $schema['email'] = $settings->email;
        }

        if ($settings->address) {
            $schema['address'] = ['@type' => 'PostalAddress', 'streetAddress' => $settings->address];
        }

        if (! empty($settings->social_links)) {
            $schema['sameAs'] = $settings->social_links;
        }

        return $schema;
    }
}

if (! function_exists('device_label')) {
    /**
     * A best-effort "Browser on OS" label parsed from a User-Agent string,
     * for display on Active Sessions / Login History screens. Intentionally
     * simple regex matching rather than a full UA-parser dependency.
     */
    function device_label(?string $userAgent): string
    {
        if (blank($userAgent)) {
            return 'Unknown Device';
        }

        $os = match (true) {
            (bool) preg_match('/windows/i', $userAgent) => 'Windows',
            (bool) preg_match('/iphone/i', $userAgent) => 'iPhone',
            (bool) preg_match('/ipad/i', $userAgent) => 'iPad',
            (bool) preg_match('/mac os x/i', $userAgent) => 'macOS',
            (bool) preg_match('/android/i', $userAgent) => 'Android',
            (bool) preg_match('/linux/i', $userAgent) => 'Linux',
            default => null,
        };

        $browser = match (true) {
            (bool) preg_match('/edg\//i', $userAgent) => 'Edge',
            (bool) preg_match('/chrome\//i', $userAgent) => 'Chrome',
            (bool) preg_match('/crios\//i', $userAgent) => 'Chrome',
            (bool) preg_match('/firefox\//i', $userAgent) => 'Firefox',
            (bool) preg_match('/safari\//i', $userAgent) => 'Safari',
            default => null,
        };

        return match (true) {
            $browser && $os => "{$browser} on {$os}",
            (bool) $browser => $browser,
            (bool) $os => $os,
            default => 'Unknown Device',
        };
    }
}

if (! function_exists('resolve_internal_links')) {
    /**
     * Turns root-relative links in admin-written HTML (href="/contact")
     * into absolute URLs for the current install — so content stored as
     * "/contact" works whether the site runs at the domain root (live) or
     * in a subfolder (local), and crawlers always get a full URL.
     */
    function resolve_internal_links(?string $html): string
    {
        return preg_replace_callback(
            '/\bhref="\/(?!\/)([^"]*)"/',
            fn ($m) => 'href="'.e(url('/'.html_entity_decode($m[1]))).'"',
            (string) $html
        );
    }
}
