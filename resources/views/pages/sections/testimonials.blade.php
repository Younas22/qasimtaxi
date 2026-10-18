@props(['section'])

@php
    $settings = $section->testimonial_settings;
    $testimonials = \App\Models\Review::approved()
        ->where('rating', '>=', $settings['min_rating'])
        ->whereNotNull('comment')
        ->with(['customer', 'booking'])
        ->orderByDesc('is_featured')
        ->latest()
        ->limit($settings['limit'])
        ->get();
@endphp

@if ($testimonials->isNotEmpty())
    <section class="border-b border-luxury-border" x-data="testimonialSlider()">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            @if ($section->heading || $section->subheading)
                <div class="mx-auto max-w-2xl text-center">
                    @if ($section->heading)
                        <h2 class="text-2xl font-semibold text-luxury-white sm:text-3xl">{{ __($section->heading) }}</h2>
                    @endif
                    @if ($section->subheading)
                        <p class="mt-3 text-luxury-muted">{{ __($section->subheading) }}</p>
                    @endif
                </div>
            @endif

            <div class="relative mt-10">
                <button type="button" @click="scrollPrev" aria-label="{{ __('Previous') }}"
                    class="absolute -start-4 top-1/2 z-10 hidden h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full border border-luxury-border bg-luxury-charcoal text-luxury-muted shadow-lg transition hover:border-luxury-gold/40 hover:text-luxury-gold lg:flex">
                    <x-icon name="chevron-left" class="h-4 w-4" />
                </button>

                <div x-ref="slider" class="scrollbar-luxury flex snap-x snap-mandatory gap-5 overflow-x-auto scroll-smooth pb-4">
                    @foreach ($testimonials as $review)
                        <div class="flex w-[85%] shrink-0 snap-start flex-col rounded-2xl border border-luxury-border bg-luxury-charcoal p-6 sm:w-[46%] lg:w-[31%]">
                            <x-rating-stars :rating="$review->rating" size="h-4 w-4" />
                            {{-- Real trip context pulled from the linked booking — never
                                 invented — so "great service" reads as "great service on
                                 this specific route," which the audit found materially
                                 more convincing than a generic quote. --}}
                            @if ($review->booking?->pickup_location && $review->booking?->dropoff_location)
                                <p class="mt-3 flex items-center gap-1.5 text-xs font-medium uppercase tracking-wide text-luxury-gold">
                                    <x-icon name="map-pin" class="h-3.5 w-3.5 shrink-0" />
                                    <span class="truncate">{{ $review->booking->pickup_location }} &rarr; {{ $review->booking->dropoff_location }}</span>
                                </p>
                            @endif
                            <p class="mt-3 flex-1 text-sm text-luxury-muted">&ldquo;{{ $review->comment }}&rdquo;</p>
                            <div class="mt-5 flex items-center gap-3 border-t border-luxury-border pt-4">
                                @if ($review->customer?->avatar_url)
                                    <img src="{{ $review->customer->avatar_url }}" alt="{{ $review->customer->name }}" loading="lazy"
                                        class="h-9 w-9 shrink-0 rounded-full object-cover">
                                @else
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-luxury-gold/10 text-sm font-semibold text-luxury-gold">
                                        {{ strtoupper(substr($review->customer?->name ?? 'C', 0, 1)) }}
                                    </span>
                                @endif
                                <div>
                                    <p class="text-sm font-medium text-luxury-white">{{ $review->customer?->name ?? 'Verified Customer' }}</p>
                                    <p class="text-xs text-luxury-muted">{{ $review->created_at->format('M Y') }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <button type="button" @click="scrollNext" aria-label="{{ __('Next') }}"
                    class="absolute -end-4 top-1/2 z-10 hidden h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full border border-luxury-border bg-luxury-charcoal text-luxury-muted shadow-lg transition hover:border-luxury-gold/40 hover:text-luxury-gold lg:flex">
                    <x-icon name="chevron-right" class="h-4 w-4" />
                </button>
            </div>
        </div>
    </section>
@endif

<script>
    function testimonialSlider() {
        return {
            // Slider scrolling assumes LTR reading order (native touch swipe
            // works correctly in RTL too — only these desktop arrow buttons
            // are LTR-oriented).
            scrollPrev() {
                this.$refs.slider.scrollBy({ left: -360, behavior: 'smooth' });
            },
            scrollNext() {
                this.$refs.slider.scrollBy({ left: 360, behavior: 'smooth' });
            },
        };
    }
</script>
