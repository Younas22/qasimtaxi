@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-xs font-medium uppercase tracking-wider text-luxury-muted mb-2']) }}>
    {{ $value ?? $slot }}
</label>
