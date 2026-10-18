@props(['label', 'value', 'trend' => null])

<div class="group relative overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal p-6 transition hover:border-luxury-gold/40">
    <div class="absolute -end-6 -top-6 h-24 w-24 rounded-full bg-luxury-gold/5 blur-2xl transition group-hover:bg-luxury-gold/10"></div>

    <div class="relative flex items-start justify-between">
        <div>
            <p class="text-xs font-medium uppercase tracking-wider text-luxury-muted">{{ $label }}</p>
            <p class="mt-3 text-3xl font-semibold text-luxury-white">{{ $value }}</p>

            @if ($trend)
                <p class="mt-2 text-xs text-luxury-gold">{{ $trend }}</p>
            @endif
        </div>

        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-luxury-gold/10 text-luxury-gold">
            {{ $slot }}
        </div>
    </div>
</div>
