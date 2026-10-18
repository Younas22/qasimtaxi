@props(['section', 'page' => null])

@php
    // Service pages get a compact banner (background capped to its actual
    // 1900×575 max size — see PageSectionController's upload validation)
    // instead of the homepage's full-screen cinematic hero. Passing $page
    // in from pages/show.blade.php is what lets this one shared partial
    // tell the two apart.
    $isCompactBanner = $page && \App\Models\Page::isServiceLike($page->slug);

    // Book Now / Call Now / phone number / the Pricing modal / any custom
    // link an admin has added — each with its own desktop/mobile
    // visibility for the hero (this section) and, separately, for the top
    // navbar (see components/header.blade.php, which reads this same list
    // off the home page's hero). See PageSection::getHeroButtonsAttribute()
    // for the legacy-column fallback used until this section is re-saved.
    $heroButtons = collect($section->hero_buttons)->filter(function ($button) {
        if (! ($button['hero_show_desktop'] ?? true) && ! ($button['hero_show_mobile'] ?? true)) {
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
        'show_desktop' => $button['hero_show_desktop'] ?? true,
        'show_mobile' => $button['hero_show_mobile'] ?? true,
    ]))->values();
@endphp

@if ($isCompactBanner)
    @php
        $showBookingWidget = booking_setting('website_booking_enabled') && booking_setting('guest_booking_enabled');
    @endphp

    {{-- theme-dark-scope: same reasoning as the full hero below — the photo
         backdrop always carries a dark gradient for text legibility,
         regardless of the site's light/dark theme.

         Mobile and desktop deliberately behave differently here, not just
         at different sizes of the same layout:
           - Mobile: the banner sizes itself to its own text content (no
             fixed aspect ratio — 1900:575 at a 390px-wide phone would be
             ~120px tall, nowhere near enough room for a heading), and the
             booking widget sits in normal flow right below it, on the
             page's plain background rather than overlapping the photo.
           - sm and up: the banner is the fixed 1900×575 banner, text is
             pinned toward its top, and the widget overlaps the lower part
             of it via a negative top margin (see below) rather than
             absolute positioning, so it stays in normal document flow and
             whatever follows on the page always starts right after it —
             no manually-measured gap to keep re-tuning as content or
             screen width changes. [container-type:inline-size] is what
             lets that negative margin be expressed as a fraction of the
             banner's own rendered width (via cqw units) instead of a
             fixed pixel guess. --}}
    <section class="theme-dark-scope relative mx-auto w-full border-b border-luxury-border [container-type:inline-size] {{ $showBookingWidget ? 'hero-compact-banner' : '' }}" style="max-width: 1900px;">
        <div class="relative w-full overflow-hidden sm:aspect-[1900/575]">
            <div class="absolute inset-0 overflow-hidden">
                @if ($section->image_url)
                    <img src="{{ $section->image_url }}" alt="" class="h-full w-full object-cover">
                @else
                    <div class="h-full w-full bg-gradient-to-br from-luxury-charcoal via-luxury-black to-luxury-black"></div>
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-luxury-black via-luxury-black/70 to-luxury-black/30"></div>
                <div class="absolute inset-0 bg-gradient-to-r from-luxury-black/40 via-transparent to-luxury-black/40"></div>
            </div>

            <div class="relative z-[1] mx-auto flex w-full max-w-3xl flex-col items-center px-4 py-10 text-center sm:absolute sm:inset-x-0 sm:top-0 sm:px-6 sm:py-0 sm:pt-9 lg:pt-12 lg:px-8">
                @if ($section->eyebrow)
                    <p class="animate-fade-up inline-flex items-center gap-1.5 rounded-full border border-luxury-gold/30 bg-luxury-gold/10 px-3 py-1 text-xs font-semibold text-luxury-gold">
                        {{ __($section->eyebrow) }}
                    </p>
                @endif

                @if ($section->heading)
                    {{-- Noticeably smaller than the homepage's full-screen hero
                         heading (which tops out at the same lg:text-7xl this
                         used to share) — this banner is a fraction of that
                         hero's height (aspect-[1900/575]), so a long heading
                         at that size doesn't just look oversized, it overflows
                         past two lines and spills out of the banner. This
                         scale keeps even a long admin-written heading (e.g. a
                         full sentence with a subtitle after a dash) wrapping
                         to two lines instead of three or four. --}}
                    <h1 class="animate-fade-up text-xl font-bold leading-tight tracking-tight text-luxury-white sm:text-2xl lg:text-4xl {{ $section->eyebrow ? 'mt-3' : '' }}">
                        {{ __($section->heading) }}
                    </h1>
                @endif

                @if ($section->subheading)
                    <p class="animate-fade-up delay-1 mx-auto mt-3 max-w-xl text-sm text-luxury-muted sm:text-base">
                        {{ __($section->subheading) }}
                    </p>
                @endif

                @if ($heroButtons->isNotEmpty())
                    <div class="animate-fade-up delay-2 mt-5 flex flex-row items-center justify-center gap-2 sm:mt-6 sm:gap-3">
                        @foreach ($heroButtons as $button)
                            <x-hero-button :button="$button" size="compact" />
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Booking search box. Mobile: plain stacked block below the
             banner, own solid background, no overlap. sm and up: pulled up
             by a negative top margin equal to a quarter of the banner's own
             height (25% of 30.2632cqw, i.e. 575/1900 of the container's
             width) — low enough to clear the top-aligned text above, while
             still genuinely overlapping (not just touching) the photo's
             bottom edge. Because this is a negative margin on an
             in-flow element rather than absolute positioning, the section
             (and whatever comes after it) grows to match automatically —
             no separate spacer to keep re-measuring. --}}
        @if ($showBookingWidget)
            <div class="hero-compact-widget relative z-10 mx-auto w-full max-w-5xl px-4 py-8 sm:px-6 sm:py-0 sm:mt-[-7.5658cqw] lg:px-8">
                <x-booking-search-box />
            </div>
        @endif
    </section>

    @if ($showBookingWidget)
        {{-- Every other section partial gives itself a generous py-16 (or
             equivalent) top padding, tuned for sitting under a plain page
             heading — not for sitting right under a booking widget that's
             already right there thanks to the negative margin above.
             Trimming just that top padding on whichever section happens to
             render right after this one keeps the widget and the page's
             next content close together, without editing every section
             partial (there's no shared "content section" base to hook
             into) or assuming which section type comes next. Left
             untouched on mobile, where the widget is a normal stacked
             block and the next section's own spacing already looks right. --}}
        <style>
            @media (min-width: 640px) {
                .hero-compact-banner + section { padding-top: 0 !important; }
                .hero-compact-banner + section > div:first-child { padding-top: 0.375rem !important; }
            }
        </style>
    @endif
@else
    {{-- theme-dark-scope: the hero's photo/video backdrop always carries a dark
         gradient for text legibility, regardless of the site's light/dark
         theme — this keeps its text readable in both. See app.css. --}}
    <section class="theme-dark-scope relative flex min-h-[100svh] flex-col justify-center border-b border-luxury-border">
        {{-- Background media — overflow-hidden lives here (not on the section) so the
             Ken Burns zoom stays contained without clipping the booking widget's
             dropdowns, which need to be able to render outside the hero's bounds. --}}
        <div class="absolute inset-0 overflow-hidden">
            @if ($section->video_url)
                <video class="h-full w-full object-cover animate-ken-burns" autoplay muted loop playsinline
                    @if ($section->image_url) poster="{{ $section->image_url }}" @endif>
                    <source src="{{ $section->video_url }}" type="video/mp4">
                </video>
            @elseif ($section->image_url)
                <img src="{{ $section->image_url }}" alt="" class="h-full w-full object-cover animate-ken-burns">
            @else
                <div class="h-full w-full bg-gradient-to-br from-luxury-charcoal via-luxury-black to-luxury-black"></div>
            @endif
            <div class="absolute inset-0 bg-gradient-to-t from-luxury-black via-luxury-black/75 to-luxury-black/30"></div>
            <div class="absolute inset-0 bg-gradient-to-r from-luxury-black/50 via-transparent to-luxury-black/50"></div>
        </div>

        {{-- Content --}}
        <div class="relative mx-auto flex w-full max-w-5xl flex-1 flex-col items-center justify-center px-4 py-28 text-center sm:px-6 lg:px-8">
            @if ($section->eyebrow)
                <p class="animate-fade-up inline-flex items-center gap-1.5 rounded-full border border-luxury-gold/30 bg-luxury-gold/10 px-4 py-1.5 text-xs font-semibold text-luxury-gold sm:text-sm">
                    {{ __($section->eyebrow) }}
                </p>
            @endif

            @if ($section->heading)
                <h1 class="animate-fade-up text-4xl font-bold leading-[0.95] tracking-tight text-luxury-white sm:text-6xl lg:text-7xl {{ $section->eyebrow ? 'mt-4' : '' }}">
                    {{ __($section->heading) }}
                </h1>
            @endif

            @if ($section->subheading)
                <p class="animate-fade-up delay-1 mx-auto mt-6 max-w-2xl text-base text-luxury-muted sm:text-lg">
                    {{ __($section->subheading) }}
                </p>
            @endif

            {{-- A short, specific claim — deliberately smaller and more compact
                 than the subheading so it reads as a stated fact, not more
                 marketing copy. Admin-entered only; never auto-filled, since an
                 untrue claim here would cost more trust than none at all. --}}
            @if ($section->differentiator)
                <p class="animate-fade-up delay-1 mt-4 inline-flex items-center gap-2 rounded-full border border-luxury-gold/30 bg-luxury-gold/10 px-4 py-1.5 text-xs font-semibold text-luxury-gold sm:text-sm">
                    <x-icon name="check-circle" class="h-4 w-4 shrink-0" />
                    {{ __($section->differentiator) }}
                </p>
            @endif

            @if ($heroButtons->isNotEmpty())
                <div class="animate-fade-up delay-2 mt-9 flex flex-row items-center justify-center gap-3">
                    @foreach ($heroButtons as $button)
                        <x-hero-button :button="$button" size="full" />
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Booking search box (renders nothing when website booking is off) --}}
        @if (booking_setting('website_booking_enabled') && booking_setting('guest_booking_enabled'))
            <div class="relative z-10 mx-auto w-full max-w-5xl px-4 pb-12 sm:px-6 lg:px-8">
                <x-booking-search-box />
            </div>
        @endif
    </section>
@endif
