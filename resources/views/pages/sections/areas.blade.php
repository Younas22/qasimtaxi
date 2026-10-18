@props(['section'])

@php
    $areas = \App\Models\Area::active()->ordered()->get();
@endphp

@if ($areas->isNotEmpty())
    <section class="border-b border-luxury-border">
        <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
            @if ($section->heading || $section->subheading)
                <div class="mx-auto max-w-2xl text-center">
                    @if ($section->heading)
                        <{{ $headingTag ?? 'h2' }} class="text-2xl font-semibold text-luxury-white sm:text-3xl">{{ __($section->heading) }}</{{ $headingTag ?? 'h2' }}>
                    @endif
                    @if ($section->subheading)
                        <p class="mt-3 text-luxury-muted">{{ __($section->subheading) }}</p>
                    @endif
                </div>
            @endif

            <div class="mt-10 grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5"
                x-data="{ armed: false, visible: false }" x-init="armed = true" x-intersect.once="visible = true">
                @foreach ($areas as $area)
                    <a href="{{ route('areas.show', $area) }}"
                        class="reveal-up delay-{{ ($loop->index % 6) + 1 }} flex items-center gap-2 rounded-xl border border-luxury-border bg-luxury-charcoal px-4 py-3 transition duration-300 hover:-translate-y-0.5 hover:border-luxury-gold/40"
                        :class="{ 'reveal-armed': armed, 'is-visible': visible }">
                        <x-icon name="map-pin" class="h-4 w-4 shrink-0 text-luxury-gold" />
                        <span class="truncate text-sm font-medium text-luxury-white">{{ $area->name }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
