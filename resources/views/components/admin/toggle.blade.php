@props(['name', 'checked' => false, 'label' => null, 'description' => null])

<label class="flex cursor-pointer items-start justify-between gap-4 rounded-xl border border-luxury-border bg-luxury-graphite/40 p-4 transition hover:border-luxury-gold/30">
    <span class="min-w-0">
        @if ($label)
            <span class="block text-sm font-medium text-luxury-white">{{ $label }}</span>
        @endif
        @if ($description)
            <span class="mt-1 block text-xs text-luxury-muted">{{ $description }}</span>
        @endif
    </span>

    <span class="relative inline-flex shrink-0 items-center">
        <input type="hidden" name="{{ $name }}" value="0">
        <input type="checkbox" name="{{ $name }}" value="1" @checked($checked)
            {{ $attributes->merge(['class' => 'peer sr-only']) }}>
        <span class="h-6 w-11 rounded-full bg-luxury-slate transition peer-checked:bg-luxury-gold peer-focus-visible:ring-2 peer-focus-visible:ring-luxury-gold peer-focus-visible:ring-offset-2 peer-focus-visible:ring-offset-luxury-black"></span>
        <span class="absolute start-1 top-1 h-4 w-4 rounded-full bg-luxury-white transition-transform peer-checked:translate-x-5 rtl:peer-checked:-translate-x-5"></span>
    </span>
</label>
