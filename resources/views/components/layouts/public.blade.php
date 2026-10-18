@props([
    'title' => null,
    'description' => null,
    'currentSlug' => null,
    'ogImage' => null,
    'ogType' => 'website',
    'publishedTime' => null,
    // Per-page SEO overrides — every call site defaults to the
    // site-wide/indexable behavior that was already in place before these
    // existed, so nothing currently indexed changes unless a page opts in.
    'canonicalOverride' => null,
    'robotsIndex' => null,
    'robotsFollow' => null,
])

@php
    $navPages = $navPages ?? \App\Models\Page::where('is_active', true)->get()->keyBy('slug');
    $direction = \App\Models\Language::findActiveByCode(app()->getLocale())?->direction ?? 'ltr';
    // The public site's own dark/light preference — session-scoped per
    // visitor (see ThemeController), and deliberately independent of
    // Setting::theme_mode, which is documented as an admin/customer-panel-
    // only preference and must never change what a site visitor sees.
    $themeMode = session('public_theme_mode', 'dark');
    $pageTitle = $title ? seo_title($title) : (setting('meta_title') ?: setting('company_name', config('app.name', 'Limo Schedule')));
    $metaDescription = $description ?: setting('meta_description') ?: setting('tagline');
    $resolvedOgImage = $ogImage ?: setting('og_image_url') ?: setting('logo_url');
    $canonicalUrl = $canonicalOverride ?: url()->current();
    // The site-wide Settings → Search Engine Indexing toggle is a kill
    // switch, not just a fallback: once an admin has actually saved a page
    // through the edit form, its robots_index/robots_follow are always an
    // explicit true/false (the form has no third "inherit" state), so
    // "default only when the page hasn't set its own" almost never applies
    // in practice — turning the site-wide toggle off silently did nothing
    // once every page had been saved at least once. Indexing is now only
    // ever allowed when BOTH the site-wide toggle and the page's own value
    // (defaulting to true when truly unset) agree; either one saying no is
    // enough to noindex/nofollow — the per-page value can still tighten
    // things (noindex a specific page) but can no longer loosen them past
    // a site-wide "off".
    $robotsIndex = setting('default_robots_index', true) && ($robotsIndex ?? true);
    $robotsFollow = setting('default_robots_follow', true) && ($robotsFollow ?? true);
    $robotsContent = ($robotsIndex ? 'index' : 'noindex').', '.($robotsFollow ? 'follow' : 'nofollow');
    $schemaBuilder = app(\App\Services\SchemaBuilder::class);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $direction }}"
    {{-- The visitor's own per-session choice (see $themeMode above) — still
         entirely independent of Admin Settings → Light Mode, which only
         ever affects the admin/customer/driver panels. --}}
    data-theme="{{ $themeMode }}" class="{{ $themeMode === 'light' ? '' : 'dark' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle }}</title>
    @if ($metaDescription)
        <meta name="description" content="{{ $metaDescription }}">
    @endif
    @if (setting('meta_keywords'))
        <meta name="keywords" content="{{ setting('meta_keywords') }}">
    @endif
    @if (setting('google_site_verification'))
        <meta name="google-site-verification" content="{{ setting('google_site_verification') }}">
    @endif
    <meta name="robots" content="{{ $robotsContent }}">
    @if (setting('google_analytics_id'))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ setting('google_analytics_id') }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){ dataLayer.push(arguments); }
            gtag('js', new Date());
            gtag('config', {{ \Illuminate\Support\Js::from(setting('google_analytics_id')) }});
        </script>
    @endif
    <link rel="canonical" href="{{ $canonicalUrl }}">

    {{-- Open Graph --}}
    <meta property="og:title" content="{{ $pageTitle }}">
    @if ($metaDescription)
        <meta property="og:description" content="{{ $metaDescription }}">
    @endif
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    @if ($resolvedOgImage)
        <meta property="og:image" content="{{ $resolvedOgImage }}">
    @endif
    @if ($ogType === 'article' && $publishedTime)
        <meta property="article:published_time" content="{{ $publishedTime }}">
    @endif

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    @if ($metaDescription)
        <meta name="twitter:description" content="{{ $metaDescription }}">
    @endif
    @if ($resolvedOgImage)
        <meta name="twitter:image" content="{{ $resolvedOgImage }}">
    @endif

    <meta name="theme-color" content="{{ setting('primary_color', '#c9a24b') }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <link rel="icon" href="{{ setting('favicon_url') ?: asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ setting('favicon_url') ?: asset('favicon.ico') }}">

    {{-- Site-wide structured data — LocalBusiness/TaxiService (per the
         configured business type) plus WebSite. Page-specific schema
         (FAQPage, BreadcrumbList) is emitted alongside the content it
         describes instead of threaded through this layout — see
         pages/sections/faq.blade.php and components/breadcrumbs.blade.php. --}}
    <script type="application/ld+json">{!! json_encode($schemaBuilder->organization(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    <script type="application/ld+json">{!! json_encode($schemaBuilder->website(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('admin.partials.theme-vars')

    {{-- Windows doesn't render flag emoji, so currency/language flags use this CSS sprite library instead. --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icons@7.2.3/css/flag-icons.min.css">

    {{-- Alpine plugins must load before the core Alpine script. --}}
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/focus@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/intersect@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{ $head ?? '' }}
</head>
<body class="min-h-screen bg-luxury-charcoal font-sans text-luxury-white antialiased">
    <x-page-progress />

    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:start-4 focus:top-4 focus:z-[70] focus:rounded-lg focus:bg-luxury-gold focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-luxury-black">
        {{ __('Skip to content') }}
    </a>

    <div x-data="publicSiteShell(@js($themeMode))" @keydown.escape.window="sidebarOpen = false">
        <x-notifications />

        <x-header :nav-pages="$navPages" :current-slug="$currentSlug" />

        <x-sidebar :nav-pages="$navPages" :current-slug="$currentSlug" />

        <main id="main-content">
            {{ $slot }}
        </main>

        <x-footer :nav-pages="$navPages" />

        <x-sticky-booking-button />
        <x-sticky-whatsapp-button />

        <x-bottom-nav :current-slug="$currentSlug" />

        <x-search-modal />

        <x-pricing-modal />

        <x-software-sale-modal />
        <x-software-sale-sticky-banner />
    </div>

    <script>
        // Shared shell state for the whole public site: the mobile sidebar,
        // the search overlay, and the visitor's own dark/light preference —
        // lifted up here (rather than scoped to just the header) so the
        // header, the mobile menu, and the bottom nav can all read/change
        // the same theme state. Session-scoped via ThemeController, and
        // entirely independent of Setting::theme_mode (the admin/customer/
        // driver panels' theme) — see the same pattern in
        // components/customer/layouts/app.blade.php.
        function publicSiteShell(initialTheme) {
            return {
                sidebarOpen: false,
                searchOpen: false,
                pricingOpen: false,
                theme: initialTheme,

                toggleTheme() {
                    const mode = this.theme === 'dark' ? 'light' : 'dark';
                    this.theme = mode;
                    document.documentElement.classList.toggle('dark', mode !== 'light');
                    document.documentElement.setAttribute('data-theme', mode);

                    fetch('{{ route('theme.toggle') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                    }).catch(() => {});
                },
            };
        }
    </script>
</body>
</html>
