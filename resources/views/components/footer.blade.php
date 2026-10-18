@props(['navPages' => null])

@php
    $navPages ??= \App\Models\Page::where('is_active', true)->get()->keyBy('slug');
    $activeLanguages = \App\Models\Language::active();
    $currentLanguage = $activeLanguages->firstWhere('code', app()->getLocale()) ?? $activeLanguages->first();
    $activeCurrencies = \App\Models\Currency::active();
    $currentCurrency = active_currency();
    $socialLinks = [
        'facebook' => setting('facebook_url'),
        'instagram' => setting('instagram_url'),
        'twitter' => setting('twitter_url'),
        'linkedin' => setting('linkedin_url'),
        'youtube' => setting('youtube_url'),
    ];

    // Same admin-driven service list as the header's "Services" mega-menu
    // (components/header.blade.php) — a service page enabled/disabled or
    // renamed in the admin is reflected here automatically, with no
    // separate configuration to maintain.
    $footerServiceIcons = [
        'airport-transfer' => 'plane',
        'chauffeur-service' => 'user',
        'corporate-transfer' => 'briefcase',
        'city-rides' => 'car',
        'hourly-rides' => 'clock',
        'vip-transport' => 'sparkles',
    ];
    $footerServiceLinks = collect(\App\Models\Page::SERVICE_PAGES)
        ->filter(fn ($slug) => isset($navPages[$slug]))
        ->map(fn ($slug) => ['slug' => $slug, 'label' => \App\Models\Page::PAGES[$slug]]);
@endphp

<footer class="border-t border-luxury-border bg-luxury-charcoal pb-20 lg:pb-0">
    <div class="mx-auto max-w-7xl px-4 pt-16 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-10 sm:grid-cols-2 lg:grid-cols-4 lg:gap-8">
            {{-- Quick links --}}
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-luxury-white">{{ __('Quick Links') }}</p>
                <span class="mt-2 block h-0.5 w-8 rounded-full bg-luxury-gold"></span>
                <ul class="mt-4 space-y-2.5">
                    @foreach (\App\Models\Page::PAGES as $slug => $label)
                        @continue(! isset($navPages[$slug]))
                        @continue(\App\Models\Page::isExcludedFromNav($slug))
                        <li>
                            <a href="{{ $slug === 'home' ? route('pages.home') : route('pages.show', $slug) }}"
                                class="group flex items-center gap-1.5 text-sm text-luxury-muted transition hover:text-luxury-gold">
                                <x-icon name="chevron-right" class="h-3.5 w-3.5 shrink-0 text-luxury-gold/60 transition-transform duration-200 rtl:rotate-180 group-hover:translate-x-1 rtl:group-hover:-translate-x-1" />
                                <span>{{ __($label) }}</span>
                            </a>
                        </li>
                    @endforeach
                    <li>
                        <a href="{{ route('blog.index') }}" class="group flex items-center gap-1.5 text-sm text-luxury-muted transition hover:text-luxury-gold">
                            <x-icon name="chevron-right" class="h-3.5 w-3.5 shrink-0 text-luxury-gold/60 transition-transform duration-200 rtl:rotate-180 group-hover:translate-x-1 rtl:group-hover:-translate-x-1" />
                            <span>{{ __('Blog') }}</span>
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Services — mirrors the header's Services mega-menu list --}}
            @if ($footerServiceLinks->isNotEmpty())
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-luxury-white">{{ __('Services') }}</p>
                    <span class="mt-2 block h-0.5 w-8 rounded-full bg-luxury-gold"></span>
                    <ul class="mt-4 space-y-2.5">
                        @foreach ($footerServiceLinks as $service)
                            <li>
                                <a href="{{ route('pages.show', $service['slug']) }}"
                                    class="group flex items-center gap-1.5 text-sm text-luxury-muted transition hover:text-luxury-gold">
                                    <x-icon name="chevron-right" class="h-3.5 w-3.5 shrink-0 text-luxury-gold/60 transition-transform duration-200 rtl:rotate-180 group-hover:translate-x-1 rtl:group-hover:-translate-x-1" />
                                    <span>{{ __($service['label']) }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Legal --}}
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-luxury-white">{{ __('Legal') }}</p>
                <span class="mt-2 block h-0.5 w-8 rounded-full bg-luxury-gold"></span>
                <ul class="mt-4 space-y-2.5">
                    @foreach (\App\Models\Page::LEGAL_PAGES as $slug)
                        @continue(! isset($navPages[$slug]))
                        <li>
                            <a href="{{ route('pages.show', $slug) }}" class="group flex items-center gap-1.5 text-sm text-luxury-muted transition hover:text-luxury-gold">
                                <x-icon name="chevron-right" class="h-3.5 w-3.5 shrink-0 text-luxury-gold/60 transition-transform duration-200 rtl:rotate-180 group-hover:translate-x-1 rtl:group-hover:-translate-x-1" />
                                <span>{{ __(\App\Models\Page::PAGES[$slug]) }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Contact --}}
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-luxury-white">{{ __('Contact') }}</p>
                <span class="mt-2 block h-0.5 w-8 rounded-full bg-luxury-gold"></span>
                <ul class="mt-4 space-y-3">
                    @if (setting('address'))
                        <li>
                            @if (setting('google_maps_embed_url'))
                                <a href="{{ setting('google_maps_embed_url') }}" target="_blank" rel="noopener" class="group flex items-start gap-3">
                            @else
                                <div class="flex items-start gap-3">
                            @endif
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-luxury-border bg-luxury-graphite/60 text-luxury-muted transition group-hover:border-luxury-gold/40 group-hover:text-luxury-gold">
                                    <x-icon name="map-pin" class="h-4 w-4" />
                                </span>
                                <span class="pt-2 text-sm leading-snug text-luxury-muted transition group-hover:text-luxury-gold">{{ setting('address') }}</span>
                            @if (setting('google_maps_embed_url'))
                                </a>
                            @else
                                </div>
                            @endif
                        </li>
                    @endif
                    @if (setting('phone'))
                        <li>
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', setting('phone')) }}" class="group flex items-center gap-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-luxury-border bg-luxury-graphite/60 text-luxury-muted transition group-hover:border-luxury-gold/40 group-hover:text-luxury-gold">
                                    <x-icon name="phone" class="h-4 w-4" />
                                </span>
                                <span class="min-w-0 truncate text-sm text-luxury-muted transition group-hover:text-luxury-gold">{{ setting('phone') }}</span>
                            </a>
                        </li>
                    @endif
                    @if (setting('whatsapp'))
                        <li>
                            <a href="https://wa.me/{{ preg_replace('/\D+/', '', setting('whatsapp')) }}?text={{ rawurlencode(__("Hi! I'd like to inquire about booking a ride.")) }}" target="_blank" rel="noopener" class="group flex items-center gap-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-luxury-border bg-luxury-graphite/60 text-[#25D366] transition group-hover:border-[#25D366]/50 group-hover:bg-[#25D366]/10">
                                    <x-whatsapp-icon class="h-4 w-4" />
                                </span>
                                <span class="min-w-0 truncate text-sm text-luxury-muted transition group-hover:text-luxury-white">{{ setting('whatsapp') }}</span>
                            </a>
                        </li>
                    @endif
                    @if (setting('email'))
                        <li>
                            <a href="mailto:{{ setting('email') }}" class="group flex items-center gap-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-luxury-border bg-luxury-graphite/60 text-luxury-muted transition group-hover:border-luxury-gold/40 group-hover:text-luxury-gold">
                                    <x-icon name="mail" class="h-4 w-4" />
                                </span>
                                {{-- The Contact column now spans 2 grid tracks (see the
                                     wrapper above) specifically so this fits on one line;
                                     truncate is just a safety net for an unusually long address. --}}
                                <span class="min-w-0 truncate text-sm text-luxury-muted transition group-hover:text-luxury-gold">{{ setting('email') }}</span>
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        </div>

        {{-- Social links — moved out of the (now removed) brand column into
             their own centered row, so they still appear once, just lower
             on the page. --}}
        @if (array_filter($socialLinks))
            <div class="mt-10 flex items-center justify-center gap-3 border-t border-luxury-border pt-8">
                @foreach ($socialLinks as $platform => $url)
                    @continue(! $url)
                    <a href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ ucfirst($platform) }}"
                        class="flex h-10 w-10 items-center justify-center rounded-lg border border-luxury-border bg-luxury-graphite/60 text-luxury-muted transition hover:border-luxury-gold/40 hover:bg-luxury-gold/10 hover:text-luxury-gold">
                        <x-social-icon :name="$platform" class="h-4 w-4" />
                    </a>
                @endforeach
            </div>
        @endif

        {{-- Trust strip — fixed, brief-specified copy rather than admin
             content: there is no existing "footer trust badges" data
             source, and the PageSection "Trust Badges" type built for
             homepage use doesn't fit a global, every-page component. --}}
        <div class="mt-10 grid grid-cols-1 gap-px overflow-hidden rounded-2xl border border-luxury-border bg-luxury-border sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['icon' => 'shield', 'title' => __('Safe & Reliable'), 'body' => __('Your safety is our top priority.')],
                ['icon' => 'clock', 'title' => __('On-Time Service'), 'body' => __('Punctual pickups and timely arrivals.')],
                ['icon' => 'users', 'title' => __('Professional Drivers'), 'body' => __('Experienced and courteous drivers.')],
                ['icon' => 'chat', 'title' => __('24/7 Support'), 'body' => __("We're here whenever you need us.")],
            ] as $trust)
                <div class="flex items-start gap-3 bg-luxury-graphite/60 p-5">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-luxury-gold/10 text-luxury-gold">
                        <x-icon :name="$trust['icon']" class="h-5 w-5" />
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wide text-luxury-white">{{ $trust['title'] }}</p>
                        <p class="mt-1 text-xs leading-snug text-luxury-muted">{{ $trust['body'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Bottom bar --}}
        <div class="mt-10 grid grid-cols-1 items-center gap-4 border-t border-luxury-border py-6 sm:grid-cols-3">
            <p class="text-center text-xs text-luxury-muted sm:text-start">
                &copy; {{ now()->year }} {{ setting('company_name', config('app.name')) }}. {{ __('All rights reserved.') }}
            </p>

            <p class="text-center text-xs text-luxury-muted">
                {{ __('Powered by') }} <a href="https://limoschedule.com/" target="_blank" rel="noopener" class="font-semibold text-luxury-gold transition hover:text-luxury-gold-light">LimoSchedule</a>
            </p>

            <div class="flex flex-wrap items-center justify-center gap-2.5 sm:justify-end">
                @if ($currentCurrency && $activeCurrencies->count() > 1)
                    <div x-data="{ open: false }" class="relative">
                        <button type="button" @click="open = !open" @click.outside="open = false"
                            class="flex cursor-pointer items-center gap-1.5 rounded-lg border border-luxury-border px-3 py-2 text-xs text-luxury-muted">
                            <span class="fi fi-{{ $currentCurrency->flag_country_code }} rounded-sm"></span>
                            <span class="font-semibold">{{ $currentCurrency->symbol }}</span> {{ $currentCurrency->code }}
                        </button>
                        <div x-show="open" x-cloak x-transition class="absolute end-0 bottom-full z-40 mb-2 w-44 overflow-hidden rounded-xl border border-luxury-border bg-luxury-graphite shadow-xl">
                            @foreach ($activeCurrencies as $currencyOption)
                                <form method="POST" action="{{ route('currency.switch', $currencyOption->code) }}">
                                    @csrf
                                    <button type="submit" class="flex w-full cursor-pointer items-center gap-2 px-3.5 py-2.5 text-start text-xs {{ $currencyOption->code === $currentCurrency->code ? 'text-luxury-gold' : 'text-luxury-muted' }}">
                                        <span class="fi fi-{{ $currencyOption->flag_country_code }} shrink-0 rounded-sm"></span>
                                        {{ $currencyOption->symbol }} {{ $currencyOption->code }}
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($currentLanguage && $activeLanguages->count() > 1)
                    <div x-data="{ open: false }" class="relative">
                        <button type="button" @click="open = !open" @click.outside="open = false"
                            class="flex cursor-pointer items-center gap-1.5 rounded-lg border border-luxury-border px-3 py-2 text-xs text-luxury-muted">
                            @if ($currentLanguage->flag_url)
                                <img src="{{ $currentLanguage->flag_url }}" alt="" class="h-3.5 w-3.5 shrink-0 rounded-sm object-cover">
                            @elseif ($currentLanguage->flag_country_code)
                                <span class="fi fi-{{ $currentLanguage->flag_country_code }}"></span>
                            @endif
                            {{ $currentLanguage->native_name }}
                        </button>
                        <div x-show="open" x-cloak x-transition class="absolute end-0 bottom-full z-40 mb-2 w-44 overflow-hidden rounded-xl border border-luxury-border bg-luxury-graphite shadow-xl">
                            @foreach ($activeLanguages as $language)
                                <form method="POST" action="{{ route('locale.switch', $language->code) }}">
                                    @csrf
                                    <button type="submit" class="flex w-full cursor-pointer items-center gap-2 px-3.5 py-2.5 text-start text-xs {{ $language->code === $currentLanguage->code ? 'text-luxury-gold' : 'text-luxury-muted' }}">
                                        @if ($language->flag_url)
                                            <img src="{{ $language->flag_url }}" alt="" class="h-3.5 w-3.5 shrink-0 rounded-sm object-cover">
                                        @elseif ($language->flag_country_code)
                                            <span class="fi fi-{{ $language->flag_country_code }}"></span>
                                        @endif
                                        {{ $language->native_name }}
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</footer>
