@php
    $isAirport = str_contains($area->name, 'Airport');
    $pageHeading = $isAirport
        ? __('Taxi to :area', ['area' => $area->name])
        : __('Taxi Service in :area', ['area' => $area->name]);
    $pageTitle = $area->meta_title ?: ($isAirport
        ? __('Taxi from Bilzen to :area', ['area' => $area->name])
        : __('Taxi :area: reserve your ride', ['area' => $area->name]));
    // Visible intro: the admin-written area description. The meta
    // description is kept separate and within ~155 characters so search
    // results don't cut it off mid-sentence.
    $introText = $area->description
        ? __($area->description)
        : __('Reliable taxi service in :area — airport transfers, city rides, and fixed, upfront pricing.', ['area' => $area->name]);
    $metaDescription = $area->meta_description
        ?: (mb_strlen(strip_tags($introText)) <= 158
            ? strip_tags($introText)
            : preg_replace('/\s+\S*$/u', '', mb_substr(strip_tags($introText), 0, 155)).'…');
    // The two airports with a dedicated Bilzen landing page get a
    // contextual link to it.
    $dedicatedAirportPage = in_array($area->slug, ['maastricht-aachen-airport', 'brussels-zaventem-airport'], true)
        && isset(\App\Models\Page::PAGES['taxi-maastricht-brussels-airport'])
        && \App\Models\Page::where('slug', 'taxi-maastricht-brussels-airport')->where('is_active', true)->exists();
    $breadcrumbItems = [
        ['label' => __('Home'), 'url' => route('pages.home')],
        ['label' => __('Areas'), 'url' => route('pages.show', 'areas')],
        ['label' => $area->name, 'url' => null],
    ];
@endphp

<x-layouts.public
    :title="$pageTitle"
    :description="$metaDescription"
    current-slug="areas"
    :og-image="$area->og_image_url"
    :canonical-override="$area->canonical_override"
    :robots-index="$area->robots_index"
    :robots-follow="$area->robots_follow"
>
    <x-breadcrumbs :items="$breadcrumbItems" />

    {{-- Header --}}
    <section class="border-b border-luxury-border bg-gradient-to-br from-luxury-charcoal to-luxury-graphite">
        <div class="mx-auto max-w-5xl px-4 py-16 text-center sm:px-6 sm:py-20 lg:px-8">
            <a href="{{ route('pages.show', 'areas') }}" class="inline-flex items-center gap-1.5 text-xs font-medium text-luxury-muted transition hover:text-luxury-gold">
                <x-icon name="chevron-left" class="h-3.5 w-3.5 rtl:rotate-180" />
                {{ __('All Areas') }}
            </a>

            <div class="mt-4 flex items-center justify-center gap-2 text-luxury-gold">
                <x-icon name="map-pin" class="h-5 w-5" />
                <span class="text-sm font-semibold uppercase tracking-wide">{{ __('Service Area') }}</span>
            </div>

            <h1 class="animate-fade-up mt-3 text-3xl font-semibold leading-tight tracking-tight text-luxury-white sm:text-5xl">
                {{ $pageHeading }}
            </h1>

            <p class="animate-fade-up delay-1 mx-auto mt-5 max-w-2xl text-base text-luxury-muted">
                {{ $introText }}
            </p>

            @if ($dedicatedAirportPage)
                <p class="mx-auto mt-3 max-w-2xl text-sm text-luxury-muted">
                    {{ __('Planning your trip?') }}
                    <a href="{{ route('pages.show', 'taxi-maastricht-brussels-airport') }}" class="font-semibold text-luxury-gold underline hover:text-luxury-gold-light">{{ __('Read how a taxi from Bilzen to Maastricht Aachen Airport or Brussels Airport works') }}</a>.
                </p>
            @endif

            <div class="animate-fade-up delay-2 mt-8 flex flex-row items-center justify-center gap-3">
                <a href="{{ route('pages.home').'#booking-widget' }}"
                    class="inline-flex flex-1 items-center justify-center gap-1.5 whitespace-nowrap rounded-lg bg-luxury-gold px-4 py-3 text-xs font-semibold text-luxury-black transition hover:bg-luxury-gold-light active:scale-[0.98] sm:flex-none sm:gap-2 sm:px-7 sm:py-3.5 sm:text-sm">
                    <x-icon name="calendar" class="h-4 w-4 shrink-0" />
                    {{ __('Book Now') }}
                </a>
                @if (setting('phone'))
                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', setting('phone')) }}"
                        class="inline-flex flex-1 items-center justify-center gap-1.5 whitespace-nowrap rounded-lg border border-luxury-white/30 bg-white/5 px-4 py-3 text-xs font-semibold text-luxury-white backdrop-blur transition hover:border-luxury-white/60 hover:bg-white/10 sm:flex-none sm:gap-2 sm:px-7 sm:py-3.5 sm:text-sm">
                        <x-icon name="phone" class="h-4 w-4 shrink-0" />
                        {{ __('Call Now') }}
                    </a>
                @endif
            </div>
        </div>
    </section>

    {{-- Fleet — same live fleet showcase (filters, search, slider) used on the homepage --}}
    @include('pages.sections.fleet', ['section' => new \App\Models\PageSection([
        'type' => 'fleet',
        'heading' => __('Our Vehicle'),
        'subheading' => $isAirport
            ? __('The vehicle we\'ll send for your :area transfer.', ['area' => $area->name])
            : __('The vehicle we\'ll send for your ride in :area.', ['area' => $area->name]),
        'content' => ['limit' => 12],
    ])])

    {{-- Nearby areas --}}
    @if ($nearbyAreas->isNotEmpty())
        <section class="border-b border-luxury-border">
            <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
                <h2 class="text-center text-lg font-semibold text-luxury-white">{{ __('Also Serving Nearby') }}</h2>

                <div class="mt-8 flex flex-wrap justify-center gap-3">
                    @foreach ($nearbyAreas as $nearby)
                        <a href="{{ route('areas.show', $nearby) }}"
                            class="flex items-center gap-2 rounded-xl border border-luxury-border bg-luxury-charcoal px-4 py-3 transition hover:border-luxury-gold/40">
                            <x-icon name="map-pin" class="h-4 w-4 shrink-0 text-luxury-gold" />
                            <span class="text-sm font-medium text-luxury-white">{{ $nearby->name }}</span>
                        </a>
                    @endforeach
                </div>

                <div class="mt-6 text-center">
                    <a href="{{ route('pages.show', 'areas') }}" class="text-sm font-semibold text-luxury-gold hover:text-luxury-gold-light">
                        {{ __('View All Areas') }}
                    </a>
                </div>
            </div>
        </section>
    @endif

    {{-- CTA --}}
    <section class="border-b border-luxury-border">
        <div class="mx-auto max-w-3xl px-4 py-16 text-center sm:px-6 lg:px-8">
            <h2 class="text-2xl font-semibold text-luxury-white sm:text-3xl">{{ __('Ready to Book?') }}</h2>
            <p class="mt-3 text-luxury-muted">
                {{ $isAirport ? __('Reserve your fixed-price transfer to :area in minutes.', ['area' => $area->name]) : __('Reserve your taxi in :area in minutes.', ['area' => $area->name]) }}
            </p>
            <a href="{{ route('pages.home').'#booking-widget' }}"
                class="mt-6 inline-flex items-center justify-center gap-2 rounded-lg bg-luxury-gold px-7 py-3.5 text-sm font-semibold text-luxury-black transition hover:bg-luxury-gold-light active:scale-[0.98]">
                <x-icon name="calendar" class="h-4 w-4" />
                {{ __('Get in Touch') }}
            </a>
        </div>
    </section>
</x-layouts.public>
