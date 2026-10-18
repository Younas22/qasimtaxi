@props(['section'])

@php $stats = $section->stats; @endphp

@if (! empty($stats))
    <section class="border-b border-luxury-border bg-luxury-charcoal/40">
        <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
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

            <div class="mt-10 grid grid-cols-2 gap-6 sm:grid-cols-4"
                x-data="{ armed: false, visible: false }" x-init="armed = true" x-intersect.once="visible = true">
                @foreach ($stats as $stat)
                    <div class="reveal-up delay-{{ ($loop->index % 6) + 1 }} text-center" :class="{ 'reveal-armed': armed, 'is-visible': visible }"
                        x-data="statCounter({{ (int) ($stat['value'] ?? 0) }})" x-effect="visible && start()">
                        @if (! empty($stat['icon']))
                            <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-luxury-gold/10 text-luxury-gold">
                                <x-icon :name="$stat['icon']" class="h-6 w-6" />
                            </span>
                        @endif

                        {{-- Server-rendered fallback text (the real final number, just
                             unanimated) so the figure is never blank if Alpine fails to
                             load — x-text only overwrites it once Alpine actually runs. --}}
                        <p class="mt-3 text-3xl font-bold text-luxury-gold sm:text-4xl">
                            <span x-text="formatted">{{ number_format((int) ($stat['value'] ?? 0)) }}</span>{{ $stat['suffix'] ?? '' }}
                        </p>

                        @if (! empty($stat['label']))
                            <p class="mt-1 text-sm text-luxury-muted">{{ $stat['label'] }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif

<script>
    function statCounter(target) {
        return {
            current: 0,
            started: false,
            target,

            start() {
                if (this.started) return;
                this.started = true;

                if (!this.target) return;

                if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                    this.current = this.target;
                    return;
                }

                const duration = 1500;
                const startTime = performance.now();

                const tick = (now) => {
                    const progress = Math.min((now - startTime) / duration, 1);
                    const eased = 1 - Math.pow(1 - progress, 3);
                    this.current = Math.floor(eased * this.target);

                    if (progress < 1) {
                        requestAnimationFrame(tick);
                    } else {
                        this.current = this.target;
                    }
                };

                requestAnimationFrame(tick);
            },

            get formatted() {
                return new Intl.NumberFormat().format(this.current);
            },
        };
    }
</script>
