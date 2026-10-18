@props(['section'])

<section class="border-b border-luxury-border">
    <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
        @if ($section->subheading || $section->heading)
            <div class="mx-auto max-w-2xl text-center">
                @if ($section->subheading)
                    <p class="text-xs font-semibold uppercase tracking-wide text-luxury-gold">{{ __($section->subheading) }}</p>
                @endif
                @if ($section->heading)
                    <h2 class="mt-2 text-2xl font-semibold text-luxury-white sm:text-3xl">{{ __($section->heading) }}</h2>
                @endif
            </div>
        @endif

        <div class="mt-12 grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-4">
            @foreach ($section->process_steps as $index => $step)
                <div class="relative text-center">
                    @if (!$loop->last)
                        <div class="absolute start-1/2 top-8 hidden h-px w-full bg-luxury-border lg:block"></div>
                    @endif

                    <div class="relative mx-auto flex h-16 w-16 items-center justify-center rounded-full border border-luxury-gold/40 bg-luxury-charcoal">
                        <span class="absolute -top-2 -end-2 flex h-6 w-6 items-center justify-center rounded-full bg-luxury-gold text-xs font-bold text-luxury-black">
                            {{ $index + 1 }}
                        </span>
                        <x-icon :name="$step['icon'] ?? 'star'" class="h-6 w-6 text-luxury-gold" />
                    </div>

                    @if (!empty($step['title']))
                        <p class="mt-4 font-semibold text-luxury-white">{{ __($step['title']) }}</p>
                    @endif
                    @if (!empty($step['description']))
                        <p class="mt-2 text-sm text-luxury-muted">{{ __($step['description']) }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>
