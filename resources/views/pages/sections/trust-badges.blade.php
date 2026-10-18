@props(['section'])

@php $badges = $section->trust_badge_items; @endphp

{{--
    Deliberately a compact, wrapping row of small pills — not a 3-column
    card grid like the "items" (Why Choose Us) section — so the two remain
    visually distinct when both are used on the same page. Content is
    entirely admin-entered (icon/title/description/link, the same shape
    already validated for "items"); nothing here is auto-populated, so
    there's no risk of a fabricated claim shipping by default. Hides itself
    when empty, matching every other content section's convention.
--}}
@if (! empty($badges))
    <section class="border-b border-luxury-border">
        <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
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

            <div class="{{ ($section->heading || $section->subheading) ? 'mt-8' : '' }} flex flex-wrap items-center justify-center gap-3">
                @foreach ($badges as $badge)
                    @php $tag = ! empty($badge['link']) ? 'a' : 'div'; @endphp
                    <{{ $tag }}
                        @if (! empty($badge['link']))
                            href="{{ str_starts_with($badge['link'], 'http') ? $badge['link'] : url($badge['link']) }}"
                        @endif
                        class="flex items-center gap-2.5 rounded-full border border-luxury-border bg-luxury-charcoal px-4 py-2.5 transition {{ ! empty($badge['link']) ? 'hover:border-luxury-gold/40' : '' }}"
                    >
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-luxury-gold/10 text-luxury-gold">
                            <x-icon :name="$badge['icon'] ?? 'shield'" class="h-4 w-4" />
                        </span>
                        <span class="text-start">
                            <span class="block text-sm font-semibold leading-tight text-luxury-white">{{ __($badge['title']) }}</span>
                            @if (! empty($badge['description']))
                                <span class="block text-xs leading-tight text-luxury-muted">{{ __($badge['description']) }}</span>
                            @endif
                        </span>
                    </{{ $tag }}>
                @endforeach
            </div>
        </div>
    </section>
@endif
