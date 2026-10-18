@props(['section'])

@php
    // "Ready to Book?" -> "Ready to" (white) + "Book?" (gold) — splits off the
    // last word of whatever heading is configured, so any admin-edited
    // heading still gets the same gold-highlighted-final-word treatment
    // shown in the reference design. Translated FIRST, then split — splitting
    // the raw English heading before translation would bake the split (and
    // thus the whole heading) to English regardless of the active locale.
    $translatedHeading = $section->heading ? __($section->heading) : null;
    $headingWords = $translatedHeading ? preg_split('/\s+/', trim($translatedHeading)) : [];
    $headingLast = $headingWords ? array_pop($headingWords) : null;
    $headingLead = implode(' ', $headingWords);
@endphp

@if ($section->image_url)
    {{-- Premium boxed CTA card: dark near-black card with a hairline gold
         border, a chauffeur car + city-skyline image filling the right
         side, and a benefits row + bottom trust bar reused verbatim from
         the brief's copy (same "static, brief-specified copy" precedent
         already used for the footer's trust strip — see footer.blade.php).
         theme-dark-scope keeps the card always-dark regardless of the
         site's light/dark toggle, same reasoning as hero.blade.php. --}}
    <section class="relative border-b border-luxury-border py-14 sm:py-20 lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="theme-dark-scope relative overflow-hidden rounded-3xl border border-luxury-gold/20 bg-luxury-black shadow-2xl shadow-black/50 sm:min-h-[560px] lg:min-h-[620px]">
                {{-- Subtle gold arc behind the skyline — pure CSS, no extra image asset. --}}
                <div class="pointer-events-none absolute -right-16 -top-16 z-[5] hidden h-72 w-72 rounded-full border border-luxury-gold/40 sm:block lg:-right-10 lg:-top-10 lg:h-[26rem] lg:w-[26rem]"></div>

                {{-- Car + skyline, desktop/tablet: fills the right side, full card height. --}}
                <div class="absolute inset-y-0 right-0 hidden w-[58%] sm:block">
                    <img src="{{ $section->image_url }}" alt="" class="h-full w-full object-cover object-right">
                    {{-- Left-edge fade so the photo's own dark backdrop blends into the card instead of showing a hard seam. --}}
                    <div class="absolute inset-0 bg-gradient-to-r from-luxury-black via-luxury-black/30 to-transparent"></div>
                </div>

                {{-- Content column --}}
                <div class="relative z-10 px-6 pb-8 pt-10 sm:max-w-[52%] sm:px-10 sm:pb-10 sm:pt-14 lg:px-14 lg:pt-16">
                    {{-- Decorative line — thin gold rule, small diamond, thin gold rule. --}}
                    <div class="animate-fade-up flex items-center gap-2.5" aria-hidden="true">
                        <span class="h-px w-10 bg-luxury-gold/60"></span>
                        <span class="h-1.5 w-1.5 rotate-45 bg-luxury-gold"></span>
                        <span class="h-px w-10 bg-luxury-gold/60"></span>
                    </div>

                    @if ($section->eyebrow)
                        <p class="animate-fade-up mt-4 inline-flex items-center gap-1.5 rounded-full border border-luxury-gold/30 bg-luxury-gold/10 px-3 py-1 text-xs font-semibold text-luxury-gold">
                            {{ __($section->eyebrow) }}
                        </p>
                    @endif

                    @if ($section->heading)
                        <h2 class="animate-fade-up mt-5 text-4xl font-bold leading-tight tracking-tight text-luxury-white sm:text-5xl lg:text-6xl">
                            {{ $headingLead }}{{ $headingLead ? ' ' : '' }}<span class="text-luxury-gold">{{ $headingLast }}</span>
                        </h2>
                    @endif

                    @if ($section->subheading)
                        <p class="animate-fade-up delay-1 mt-4 text-base text-luxury-muted sm:text-lg">
                            {{ __($section->subheading) }}
                        </p>
                    @endif

                    {{-- Three compact benefits — exact copy from the brief, same static-content
                         precedent as the footer's trust strip (no per-item admin field exists,
                         and none is needed for three fixed, brief-specified claims). --}}
                    <div class="animate-fade-up delay-2 mt-8 grid grid-cols-2 gap-x-4 gap-y-5 lg:flex lg:flex-wrap lg:items-start lg:gap-x-6">
                        @php
                            $ctaBenefits = [
                                ['icon' => 'bolt', 'title' => __('Quick Booking'), 'description' => __('Book in under 60 seconds')],
                                ['icon' => 'shield', 'title' => __('Safe & Reliable'), 'description' => __('Verified drivers, every time')],
                                ['icon' => null, 'title' => __('24/7 Available'), 'description' => __("We're here whenever you need")],
                            ];
                        @endphp
                        @foreach ($ctaBenefits as $i => $benefit)
                            @if ($i > 0)
                                <div class="hidden h-9 w-px self-center bg-luxury-border lg:block"></div>
                            @endif
                            <div class="{{ $i === 2 ? 'col-span-2' : 'col-span-1' }} flex items-start gap-2.5">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-luxury-border bg-luxury-graphite">
                                    @if ($benefit['icon'])
                                        <x-icon name="{{ $benefit['icon'] }}" class="h-4 w-4 text-luxury-gold" />
                                    @else
                                        <span class="text-[11px] font-bold text-luxury-gold">24</span>
                                    @endif
                                </span>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-luxury-white">{{ $benefit['title'] }}</p>
                                    <p class="text-xs text-luxury-muted">{{ $benefit['description'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if ($section->button_text && $section->button_url)
                        <div class="animate-fade-up delay-3 mt-8">
                            <a href="{{ str_starts_with($section->button_url, 'http') ? $section->button_url : url($section->button_url) }}"
                                @if (str_starts_with($section->button_url, 'http')) target="_blank" rel="noopener" @endif
                                class="group inline-flex items-center justify-center gap-2 rounded-lg bg-luxury-gold px-7 py-3.5 text-sm font-semibold text-luxury-black shadow-lg shadow-luxury-gold/20 transition hover:bg-luxury-gold-light active:scale-[0.98]">
                                {{ __($section->button_text) }}
                                <span class="transition-transform duration-200 group-hover:translate-x-1" aria-hidden="true">&rarr;</span>
                            </a>
                        </div>
                    @endif
                </div>

                {{-- Car + skyline, mobile: own visible block below the content, per the
                     brief's required stacking order (content, then the car). --}}
                <div class="relative z-10 mt-2 h-56 sm:hidden">
                    <img src="{{ $section->image_url }}" alt="" class="h-full w-full object-cover object-[68%_center]">
                    <div class="absolute inset-0 bg-gradient-to-t from-luxury-black/50 via-transparent to-transparent"></div>
                </div>

                {{-- Bottom trust/contact bar — real data where a source already exists
                     (phone, WhatsApp, live review average), brief-specified labels
                     otherwise. Sits as its own strip so it visually crosses over the
                     lower part of the card, in front of the photo's ground/reflection. --}}
                @php
                    $ctaAvgRating = \App\Models\Review::approved()->avg('rating');
                    $ctaReviewCount = \App\Models\Review::approved()->count();
                    $ctaWaDigits = setting('whatsapp') ? preg_replace('/\D+/', '', setting('whatsapp')) : null;
                @endphp
                <div class="relative z-20 mx-4 mb-5 mt-6 rounded-2xl border border-luxury-border/70 bg-luxury-graphite/80 px-5 py-4 backdrop-blur-sm sm:mx-8 sm:mb-8 sm:mt-8 lg:mx-10">
                    <div class="grid grid-cols-1 divide-y divide-luxury-border/60 sm:grid-cols-3 sm:divide-x sm:divide-y-0">
                        <div class="flex items-center gap-3 py-3 first:pt-0 last:pb-0 sm:px-5 sm:py-0 sm:first:pl-0 sm:last:pr-0">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-luxury-border bg-luxury-charcoal">
                                <x-icon name="phone" class="h-4 w-4 text-luxury-gold" />
                            </span>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-luxury-white">{{ __('Call Us Anytime') }}</p>
                                <p class="truncate text-xs text-luxury-muted">{{ setting('phone') ?: __('Get in touch anytime') }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 py-3 first:pt-0 last:pb-0 sm:px-5 sm:py-0 sm:first:pl-0 sm:last:pr-0">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-luxury-border bg-luxury-charcoal">
                                <x-icon name="chat" class="h-4 w-4 text-luxury-gold" />
                            </span>
                            <div class="min-w-0">
                                @if ($ctaWaDigits)
                                    <a href="https://wa.me/{{ $ctaWaDigits }}" target="_blank" rel="noopener" class="text-sm font-semibold text-luxury-white transition hover:text-luxury-gold">{{ __('Live Support') }}</a>
                                @else
                                    <p class="text-sm font-semibold text-luxury-white">{{ __('Live Support') }}</p>
                                @endif
                                <p class="truncate text-xs text-luxury-muted">{{ __('We reply in minutes') }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 py-3 first:pt-0 last:pb-0 sm:px-5 sm:py-0 sm:first:pl-0 sm:last:pr-0">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-luxury-border bg-luxury-charcoal">
                                <x-icon name="star" class="h-4 w-4 text-luxury-gold" />
                            </span>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-luxury-white">{{ __('Trusted by Thousands') }}</p>
                                @if ($ctaReviewCount > 0)
                                    <div class="mt-0.5 flex items-center gap-1.5">
                                        <span class="text-xs text-luxury-muted">{{ number_format($ctaAvgRating, 1) }}</span>
                                        <x-rating-stars :rating="$ctaAvgRating" size="h-3 w-3" />
                                    </div>
                                @else
                                    <div class="mt-0.5 flex items-center gap-1.5">
                                        <span class="text-xs text-luxury-muted">5.0</span>
                                        <x-rating-stars :rating="5" size="h-3 w-3" />
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@else
    {{-- No image configured for this page's CTA — same card treatment
         (border, decorative line, gold-highlighted final word) kept as a
         single centered column, without the benefits/trust bar built for
         the flagship version above. Not forced dark — without a photo
         backdrop needing legibility treatment, it should still respect
         the site's light/dark toggle like every other plain section. --}}
    <section class="relative border-b border-luxury-border py-14 sm:py-20">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="relative overflow-hidden rounded-3xl border border-luxury-gold/20 bg-luxury-charcoal/60 px-6 py-12 text-center sm:px-12 sm:py-16">
                <div class="animate-fade-up mx-auto flex items-center justify-center gap-2.5" aria-hidden="true">
                    <span class="h-px w-10 bg-luxury-gold/60"></span>
                    <span class="h-1.5 w-1.5 rotate-45 bg-luxury-gold"></span>
                    <span class="h-px w-10 bg-luxury-gold/60"></span>
                </div>

                @if ($section->eyebrow)
                    <p class="animate-fade-up mt-4 inline-flex items-center gap-1.5 rounded-full border border-luxury-gold/30 bg-luxury-gold/10 px-3 py-1 text-xs font-semibold text-luxury-gold">
                        {{ __($section->eyebrow) }}
                    </p>
                @endif

                @if ($section->heading)
                    <h2 class="animate-fade-up mt-5 text-3xl font-bold leading-tight tracking-tight text-luxury-white sm:text-4xl">
                        {{ $headingLead }}{{ $headingLead ? ' ' : '' }}<span class="text-luxury-gold">{{ $headingLast }}</span>
                    </h2>
                @endif

                @if ($section->subheading)
                    <p class="animate-fade-up delay-1 mt-4 text-base text-luxury-muted sm:text-lg">
                        {{ __($section->subheading) }}
                    </p>
                @endif

                @if ($section->button_text && $section->button_url)
                    <div class="animate-fade-up delay-2 mt-8">
                        <a href="{{ str_starts_with($section->button_url, 'http') ? $section->button_url : url($section->button_url) }}"
                            @if (str_starts_with($section->button_url, 'http')) target="_blank" rel="noopener" @endif
                            class="group inline-flex items-center justify-center gap-2 rounded-lg bg-luxury-gold px-7 py-3.5 text-sm font-semibold text-luxury-black transition hover:bg-luxury-gold-light active:scale-[0.98]">
                            {{ __($section->button_text) }}
                            <span class="transition-transform duration-200 group-hover:translate-x-1" aria-hidden="true">&rarr;</span>
                        </a>
                    </div>
                @endif

                @if ($section->differentiator)
                    <p class="animate-fade-up delay-3 mt-6 text-xs text-luxury-muted">
                        {{ __($section->differentiator) }}
                    </p>
                @endif
            </div>
        </div>
    </section>
@endif
