@props(['navPages' => null, 'currentSlug' => null])

{{--
    Assumes it renders within a parent scope exposing Alpine state
    `sidebarOpen` and `searchOpen` (declared once on layouts.public).
--}}

@php
    $navPages ??= \App\Models\Page::where('is_active', true)->get()->keyBy('slug');
    $activeLanguages = \App\Models\Language::active();
    $currentLanguage = $activeLanguages->firstWhere('code', app()->getLocale()) ?? $activeLanguages->first();
    $activeCurrencies = \App\Models\Currency::active();
    $currentCurrency = active_currency();

    // Book Now / Call Now / phone number / Pricing / any custom link for the
    // top navbar — the SAME list an admin manages at Admin → Pages → Home →
    // Hero section (its "In the Top Navbar" toggles), so both places are
    // configured together in one spot rather than drifting independently.
    // See PageSection::getHeroButtonsAttribute() for the fallback used
    // before that section has ever been saved with this feature.
    $navbarButtons = collect(
        (isset($navPages['home']) ? $navPages['home'] : \App\Models\Page::where('slug', 'home')->first())
            ?->sections()->where('type', 'hero')->first()?->hero_buttons ?? []
    )->filter(function ($button) {
        if (! ($button['navbar_show_desktop'] ?? true) && ! ($button['navbar_show_mobile'] ?? true)) {
            return false;
        }

        if (in_array($button['kind'] ?? null, ['call_now', 'phone_number'], true)) {
            return filled(setting('phone'));
        }

        if (in_array($button['kind'] ?? null, ['book_now', 'custom'], true)) {
            return filled($button['url'] ?? null);
        }

        return true;
    })->map(fn ($button) => array_merge($button, [
        'show_desktop' => $button['navbar_show_desktop'] ?? true,
        'show_mobile' => $button['navbar_show_mobile'] ?? true,
    ]))->values();

    // Mega-menu contents for the "Services" and "Areas" nav items — spread
    // across a 4-column grid on hover instead of one long single-column
    // dropdown, so every item stays visible and scannable at a glance.
    $navServiceIcons = [
        'airport-transfer' => 'plane',
        'chauffeur-service' => 'user',
        'corporate-transfer' => 'briefcase',
        'city-rides' => 'car',
        'hourly-rides' => 'clock',
        'vip-transport' => 'sparkles',
    ];
    $navServiceLinks = collect(\App\Models\Page::SERVICE_PAGES)
        ->filter(fn ($slug) => isset($navPages[$slug]))
        ->map(fn ($slug) => ['slug' => $slug, 'label' => \App\Models\Page::PAGES[$slug], 'icon' => $navServiceIcons[$slug] ?? 'car']);
    $navAreas = isset($navPages['areas']) ? \App\Models\Area::active()->ordered()->get() : collect();

    // Whichever guard is logged in (customer, admin, or driver — a visitor
    // can only be authenticated on one at a time) drives the navbar's
    // account state, so it's always obvious someone is logged in no matter
    // which panel their account belongs to.
    $publicNavAccount = null;
    if (auth()->guard('customer')->check()) {
        $navUser = auth()->guard('customer')->user();
        $publicNavAccount = [
            'name' => $navUser->name, 'email' => $navUser->email, 'avatar' => $navUser->avatar_url,
            'dashboard' => route('customer.dashboard'), 'dashboard_label' => __('My Account'),
            'bookings' => route('customer.bookings.index'), 'logout' => route('customer.logout'),
        ];
    } elseif (auth()->guard('admin')->check()) {
        $navUser = auth()->guard('admin')->user();
        $publicNavAccount = [
            'name' => $navUser->name, 'email' => $navUser->email, 'avatar' => $navUser->avatar_url,
            'dashboard' => route('admin.dashboard'), 'dashboard_label' => __('Admin Panel'),
            'bookings' => null, 'logout' => route('admin.logout'),
        ];
    } elseif (auth()->guard('driver')->check()) {
        $navUser = auth()->guard('driver')->user();
        $publicNavAccount = [
            'name' => $navUser->name, 'email' => $navUser->email, 'avatar' => $navUser->photo_url,
            'dashboard' => route('driver.dashboard'), 'dashboard_label' => __('Driver Panel'),
            'bookings' => null, 'logout' => route('driver.logout'),
        ];
    }
@endphp

<header class="sticky top-0 z-30 border-b border-luxury-border bg-luxury-charcoal/90 backdrop-blur">
    {{-- Mobile-only utility strip: Book Now / Call / Price / custom links
         marked "Show on Mobile" (Admin → Pages → Home → Hero section) get
         their own thin row above the logo/search/hamburger row, instead of
         competing with those for space in it. Hidden entirely at sm+, where
         these same buttons render inline in the row below instead. Part of
         this same sticky header (not a separate, scrolls-away block) so the
         buttons stay reachable while scrolling, same as before. --}}
    @if ($navbarButtons->contains(fn ($button) => $button['show_mobile'] ?? true))
        <div class="flex flex-wrap items-center justify-center gap-1.5 border-b border-luxury-border/60 px-3 py-1.5 sm:hidden">
            @foreach ($navbarButtons as $button)
                <x-hero-button :button="$button" size="nav" device-context="mobile" />
            @endforeach
        </div>
    @endif

    <div class="mx-auto flex h-16 max-w-7xl items-center gap-4 px-4 sm:px-6 lg:px-8">
        {{-- Logo --}}
        <a href="{{ route('pages.home') }}" class="flex min-w-0 shrink items-center">
            @if (setting('logo_url'))
                <img src="{{ setting('logo_url') }}" alt="{{ setting('company_name') }}" class="h-7 w-auto max-w-[150px] object-contain sm:h-9 sm:max-w-[220px]">
            @else
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-luxury-gold text-sm font-bold text-luxury-black">
                    {{ strtoupper(substr(setting('company_name', 'Limo Schedule'), 0, 1)) }}
                </span>
            @endif
        </a>

        {{-- Desktop nav --}}
        <nav class="hidden flex-1 items-center justify-center gap-8 lg:flex">
            @foreach (\App\Models\Page::PAGES as $slug => $label)
                @continue(! isset($navPages[$slug]))
                @continue(\App\Models\Page::isExcludedFromNav($slug))
                @continue($slug === 'faq' || $slug === 'home')

                @if ($slug === 'services' && $navServiceLinks->isNotEmpty())
                    {{-- Alpine-driven (not CSS group-hover) so opening this
                         menu can positively close its Areas neighbor and
                         vice versa — two independent group-hover panels
                         sitting this close together will otherwise overlap
                         each other's hit-test area and block one another.
                         A short close delay keeps diagonal mouse movement
                         from the link into the panel from closing it early,
                         without needing a wide invisible bridge div. --}}
                    <div class="relative flex h-16 items-center"
                        x-data="{ open: false, closeTimer: null }"
                        @mouseenter="clearTimeout(closeTimer); $dispatch('nav-menu-open', 'services'); open = true"
                        @mouseleave="closeTimer = setTimeout(() => open = false, 200)"
                        @nav-menu-open.window="if ($event.detail !== 'services') open = false">
                        <a href="{{ route('pages.show', $slug) }}"
                            class="flex items-center gap-1 text-sm font-medium text-luxury-muted transition hover:text-luxury-gold {{ $currentSlug === $slug ? 'text-luxury-gold' : '' }}">
                            {{ __($label) }}
                            <x-icon name="chevron-down" class="h-3.5 w-3.5 transition" x-bind:class="{ 'rotate-180': open }" />
                        </a>
                        <div x-show="open" x-cloak x-transition.opacity.duration.150ms
                            class="absolute start-1/2 top-full z-40 w-[28rem] -translate-x-1/2">
                            <div class="grid grid-cols-3 gap-2 rounded-b-xl border border-t-0 border-luxury-border bg-luxury-charcoal p-4 shadow-xl">
                                @foreach ($navServiceLinks as $service)
                                    <a href="{{ route('pages.show', $service['slug']) }}"
                                        class="flex flex-col items-center gap-2 rounded-lg px-2 py-3 text-center transition hover:bg-luxury-graphite">
                                        <x-icon name="{{ $service['icon'] }}" class="h-5 w-5 text-luxury-gold" />
                                        <span class="text-xs font-medium leading-tight text-luxury-white">{{ __($service['label']) }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @elseif ($slug === 'areas' && $navAreas->isNotEmpty())
                    {{-- Same Alpine pattern as the Services menu above. --}}
                    <div class="relative flex h-16 items-center"
                        x-data="{ open: false, closeTimer: null }"
                        @mouseenter="clearTimeout(closeTimer); $dispatch('nav-menu-open', 'areas'); open = true"
                        @mouseleave="closeTimer = setTimeout(() => open = false, 200)"
                        @nav-menu-open.window="if ($event.detail !== 'areas') open = false">
                        <a href="{{ route('pages.show', $slug) }}"
                            class="flex items-center gap-1 text-sm font-medium text-luxury-muted transition hover:text-luxury-gold {{ $currentSlug === $slug ? 'text-luxury-gold' : '' }}">
                            {{ __($label) }}
                            <x-icon name="chevron-down" class="h-3.5 w-3.5 transition" x-bind:class="{ 'rotate-180': open }" />
                        </a>
                        <div x-show="open" x-cloak x-transition.opacity.duration.150ms
                            class="absolute start-1/2 top-full z-40 w-[36rem] -translate-x-1/2">
                            <div class="grid max-h-[60vh] grid-cols-4 gap-x-4 gap-y-1 overflow-y-auto rounded-b-xl border border-t-0 border-luxury-border bg-luxury-charcoal p-4 shadow-xl">
                                @foreach ($navAreas as $area)
                                    <a href="{{ route('areas.show', $area) }}"
                                        class="flex items-center gap-1.5 truncate rounded-lg px-2 py-2 text-sm text-luxury-muted transition hover:bg-luxury-graphite hover:text-luxury-white">
                                        <x-icon name="map-pin" class="h-3.5 w-3.5 shrink-0 text-luxury-gold" />
                                        <span class="truncate">{{ $area->name }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @else
                    <a href="{{ $slug === 'home' ? route('pages.home') : route('pages.show', $slug) }}"
                        class="text-sm font-medium text-luxury-muted transition hover:text-luxury-gold {{ $currentSlug === $slug ? 'text-luxury-gold' : '' }}">
                        {{ __($label) }}
                    </a>
                @endif
            @endforeach
        </nav>

        {{-- Right actions --}}
        <div class="ms-auto flex items-center gap-1.5 lg:ms-0">
            {{-- Book Now / Call Now / phone number / Pricing / custom links —
                 managed at Admin → Pages → Home → Hero section, alongside
                 the hero's own copy of the same buttons. See $navbarButtons
                 above. Desktop-only here (hidden sm:flex) — the buttons
                 marked "Show on Mobile" render instead in their own thin
                 strip above the header (see below), so this main bar never
                 gets crowded with logo + buttons + search + hamburger all
                 competing for the same tiny row. --}}
            <div class="hidden items-center gap-1.5 sm:flex">
                @foreach ($navbarButtons as $button)
                    <x-hero-button :button="$button" size="nav" device-context="desktop" />
                @endforeach
            </div>

            {{-- Search --}}
            <button type="button" @click="searchOpen = true" aria-label="{{ __('Search') }}"
                class="flex h-10 w-10 cursor-pointer items-center justify-center rounded-lg text-luxury-muted transition hover:bg-luxury-graphite hover:text-luxury-white">
                <x-icon name="search" class="h-5 w-5" />
            </button>

            {{-- Theme toggle — this visitor's own preference (session-scoped
                 via ThemeController), independent of Admin Settings → Light
                 Mode, which only ever affects the admin/customer/driver panels. --}}
            <button type="button" @click="toggleTheme()" aria-label="{{ __('Toggle appearance') }}"
                class="flex h-10 w-10 cursor-pointer items-center justify-center rounded-lg text-luxury-muted transition hover:bg-luxury-graphite hover:text-luxury-white">
                <svg x-show="theme === 'dark'" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                </svg>
                <svg x-show="theme === 'light'" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                </svg>
            </button>

            {{-- Currency switcher --}}
            @if ($currentCurrency && $activeCurrencies->count() > 1)
                <div x-data="{ open: false }" class="relative hidden sm:block">
                    <button type="button" @click="open = !open" @click.outside="open = false"
                        class="flex h-10 cursor-pointer items-center gap-1.5 rounded-lg px-2.5 text-sm text-luxury-muted transition hover:bg-luxury-graphite hover:text-luxury-white">
                        <span class="fi fi-{{ $currentCurrency->flag_country_code }} rounded-sm"></span>
                        <span class="font-semibold">{{ $currentCurrency->symbol }}</span>
                        <span>{{ $currentCurrency->code }}</span>
                        <x-icon name="chevron-down" class="h-3.5 w-3.5" />
                    </button>
                    <div x-show="open" x-cloak x-transition
                        class="absolute end-0 z-40 mt-2 w-52 overflow-hidden rounded-xl border border-luxury-border bg-luxury-charcoal shadow-xl">
                        @foreach ($activeCurrencies as $currencyOption)
                            <form method="POST" action="{{ route('currency.switch', $currencyOption->code) }}">
                                @csrf
                                <button type="submit"
                                    class="flex w-full cursor-pointer items-center gap-3 px-4 py-2.5 text-start text-sm transition hover:bg-luxury-graphite {{ $currencyOption->code === $currentCurrency->code ? 'text-luxury-gold' : 'text-luxury-muted' }}">
                                    <span class="fi fi-{{ $currencyOption->flag_country_code }} shrink-0 rounded-sm"></span>
                                    <span class="w-5 shrink-0 text-center font-semibold">{{ $currencyOption->symbol }}</span>
                                    <span>{{ $currencyOption->name }}</span>
                                    <span class="ms-auto text-xs text-luxury-muted">{{ $currencyOption->code }}</span>
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Language switcher --}}
            @if ($currentLanguage && $activeLanguages->count() > 1)
                <div x-data="{ open: false }" class="relative hidden sm:block">
                    <button type="button" @click="open = !open" @click.outside="open = false"
                        class="flex h-10 cursor-pointer items-center gap-1.5 rounded-lg px-2.5 text-sm text-luxury-muted transition hover:bg-luxury-graphite hover:text-luxury-white">
                        <div class="flex h-5 w-5 shrink-0 items-center justify-center overflow-hidden rounded border border-luxury-border bg-luxury-graphite text-[9px] font-semibold uppercase">
                            @if ($currentLanguage->flag_url)
                                <img src="{{ $currentLanguage->flag_url }}" alt="{{ $currentLanguage->name }}" class="h-full w-full object-cover">
                            @elseif ($currentLanguage->flag_country_code)
                                <span class="fi fi-{{ $currentLanguage->flag_country_code }}"></span>
                            @else
                                {{ $currentLanguage->code }}
                            @endif
                        </div>
                        <x-icon name="chevron-down" class="h-3.5 w-3.5" />
                    </button>
                    <div x-show="open" x-cloak x-transition
                        class="absolute end-0 z-40 mt-2 w-52 overflow-hidden rounded-xl border border-luxury-border bg-luxury-charcoal shadow-xl">
                        @foreach ($activeLanguages as $language)
                            <form method="POST" action="{{ route('locale.switch', $language->code) }}">
                                @csrf
                                <button type="submit"
                                    class="flex w-full cursor-pointer items-center gap-3 px-4 py-2.5 text-start text-sm transition hover:bg-luxury-graphite {{ $language->code === $currentLanguage->code ? 'text-luxury-gold' : 'text-luxury-muted' }}">
                                    <div class="flex h-5 w-5 shrink-0 items-center justify-center overflow-hidden rounded border border-luxury-border bg-luxury-graphite text-[9px] font-semibold uppercase">
                                        @if ($language->flag_url)
                                            <img src="{{ $language->flag_url }}" alt="{{ $language->name }}" class="h-full w-full object-cover">
                                        @elseif ($language->flag_country_code)
                                            <span class="fi fi-{{ $language->flag_country_code }}"></span>
                                        @else
                                            {{ $language->code }}
                                        @endif
                                    </div>
                                    <span>{{ $language->native_name }}</span>
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Auth --}}
            <div class="hidden items-center gap-2 sm:flex">
                @if ($publicNavAccount)
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" @click.outside="open = false"
                            class="tap-scale flex items-center gap-2 rounded-lg border border-luxury-border py-1.5 ps-1.5 pe-3 transition hover:border-luxury-gold/40">
                            <img src="{{ $publicNavAccount['avatar'] }}" alt="{{ $publicNavAccount['name'] }}"
                                class="h-7 w-7 rounded-md object-cover" onerror="this.src='https://ui-avatars.com/api/?background=c9a24b&color=0a0a0a&name={{ urlencode($publicNavAccount['name']) }}'">
                            <span class="text-sm font-medium text-luxury-white">{{ $publicNavAccount['name'] }}</span>
                            <x-icon name="chevron-down" class="h-4 w-4 text-luxury-muted" />
                        </button>

                        <div x-show="open" x-cloak x-transition
                            class="absolute end-0 z-40 mt-2 w-56 overflow-hidden rounded-xl border border-luxury-border bg-luxury-charcoal shadow-xl">
                            <div class="border-b border-luxury-border px-4 py-3">
                                <p class="text-sm font-semibold text-luxury-white">{{ $publicNavAccount['name'] }}</p>
                                <p class="mt-0.5 truncate text-xs text-luxury-muted">{{ $publicNavAccount['email'] }}</p>
                            </div>

                            <div class="py-1">
                                <a href="{{ $publicNavAccount['dashboard'] }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-luxury-muted hover:bg-luxury-graphite hover:text-luxury-white">
                                    <x-icon name="home" class="h-4 w-4" />
                                    {{ $publicNavAccount['dashboard_label'] }}
                                </a>
                                @if ($publicNavAccount['bookings'])
                                    <a href="{{ $publicNavAccount['bookings'] }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-luxury-muted hover:bg-luxury-graphite hover:text-luxury-white">
                                        <x-icon name="car" class="h-4 w-4" />
                                        {{ __('My Bookings') }}
                                    </a>
                                @endif
                            </div>

                            <form method="POST" action="{{ $publicNavAccount['logout'] }}" class="border-t border-luxury-border">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-2 px-4 py-2.5 text-start text-sm text-red-400 hover:bg-luxury-graphite">
                                    {{ __('Sign Out') }}
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    @if (\Illuminate\Support\Facades\Route::has('login'))
                        <a href="{{ route('login') }}" class="rounded-lg px-3.5 py-2 text-sm font-medium text-luxury-muted transition hover:text-luxury-white">
                            {{ __('Login') }}
                        </a>
                    @else
                        <span class="cursor-not-allowed rounded-lg px-3.5 py-2 text-sm font-medium text-luxury-muted/40" title="{{ __('Coming soon') }}">
                            {{ __('Login') }}
                        </span>
                    @endif

                    @if (\Illuminate\Support\Facades\Route::has('customer.register'))
                        <a href="{{ route('customer.register') }}" class="rounded-lg bg-luxury-gold px-4 py-2 text-sm font-semibold text-luxury-black transition hover:bg-luxury-gold-light">
                            {{ __('Register') }}
                        </a>
                    @else
                        <span class="cursor-not-allowed rounded-lg bg-luxury-gold/40 px-4 py-2 text-sm font-semibold text-luxury-black/60" title="{{ __('Coming soon') }}">
                            {{ __('Register') }}
                        </span>
                    @endif
                @endif
            </div>

            {{-- Hamburger (mobile) --}}
            <button type="button" @click="sidebarOpen = true" aria-label="{{ __('Menu') }}"
                class="flex h-10 w-10 items-center justify-center rounded-lg text-luxury-muted transition hover:bg-luxury-graphite hover:text-luxury-white lg:hidden">
                <x-icon name="menu" class="h-5 w-5" />
            </button>
        </div>
    </div>
</header>
