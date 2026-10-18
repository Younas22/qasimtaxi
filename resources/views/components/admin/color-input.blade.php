@props(['name', 'label', 'value' => '#000000'])

<div x-data="{ color: '{{ old($name, $value) }}' }">
    <x-admin.input-label :for="$name" :value="$label" />
    <div class="flex items-center gap-3">
        <label class="relative h-11 w-11 shrink-0 cursor-pointer overflow-hidden rounded-lg border border-luxury-border">
            <input type="color" x-model="color" class="absolute -inset-2 h-[calc(100%+1rem)] w-[calc(100%+1rem)] cursor-pointer border-0 p-0">
        </label>

        <input type="text" id="{{ $name }}" name="{{ $name }}" x-model="color"
            pattern="^#[0-9A-Fa-f]{6}$" maxlength="7" required
            class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 font-mono text-sm uppercase text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
    </div>
</div>
