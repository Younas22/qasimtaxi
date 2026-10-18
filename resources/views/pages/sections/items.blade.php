@props(['section'])

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

        <div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3"
            x-data="{ armed: false, visible: false }" x-init="armed = true" x-intersect.once="visible = true">
            @foreach ($section->items as $item)
                <div class="reveal-up delay-{{ ($loop->index % 6) + 1 }} group rounded-2xl border border-luxury-border bg-luxury-charcoal p-6 transition duration-300 hover:-translate-y-1 hover:border-luxury-gold/40 hover:shadow-xl hover:shadow-black/30"
                    :class="{ 'reveal-armed': armed, 'is-visible': visible }">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-luxury-gold/10 text-luxury-gold transition duration-300 group-hover:scale-110 group-hover:bg-luxury-gold group-hover:text-luxury-black">
                        <x-icon :name="$item['icon'] ?? 'star'" class="h-5 w-5" />
                    </span>
                    @if (! empty($item['title']))
                        <p class="mt-4 font-semibold text-luxury-white">
                            @if (! empty($item['link']))
                                <a href="{{ str_starts_with($item['link'], 'http') ? $item['link'] : url($item['link']) }}" class="hover:text-luxury-gold hover:underline">{{ __($item['title']) }}</a>
                            @else
                                {{ __($item['title']) }}
                            @endif
                        </p>
                    @endif
                    @if (! empty($item['description']))
                        <p class="mt-2 text-sm text-luxury-muted">{{ __($item['description']) }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>
