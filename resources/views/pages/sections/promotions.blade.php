@props(['section'])

@php
    $promotions = \App\Models\Promotion::active()->get();
@endphp

@if ($promotions->isNotEmpty())
    <section class="border-b border-luxury-border">
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

            <div class="{{ $section->heading || $section->subheading ? 'mt-10' : '' }} grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($promotions as $promotion)
                    <a href="{{ $promotion->link_url ? (str_starts_with($promotion->link_url, 'http') ? $promotion->link_url : url($promotion->link_url)) : '#booking-widget' }}"
                        @if ($promotion->link_url && str_starts_with($promotion->link_url, 'http')) target="_blank" rel="noopener" @endif
                        class="group relative flex h-56 flex-col justify-end overflow-hidden rounded-2xl border border-luxury-border">
                        @if ($promotion->image_url)
                            <img src="{{ $promotion->image_url }}" alt="" loading="lazy"
                                class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105">
                            <div class="absolute inset-0 bg-gradient-to-t from-luxury-black via-luxury-black/60 to-transparent"></div>
                        @else
                            <div class="absolute inset-0 bg-gradient-to-br from-luxury-graphite via-luxury-charcoal to-luxury-black"></div>
                            <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-luxury-gold/10 blur-3xl"></div>
                        @endif

                        <div class="relative p-5">
                            @if ($promotion->badge_text)
                                <span class="inline-flex items-center rounded-full bg-luxury-gold px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide text-luxury-black">
                                    {{ __($promotion->badge_text) }}
                                </span>
                            @endif
                            <h3 class="mt-2 text-lg font-semibold leading-snug text-luxury-white">{{ __($promotion->title) }}</h3>
                            @if ($promotion->subtitle)
                                <p class="mt-1 text-sm text-luxury-muted">{{ __($promotion->subtitle) }}</p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
