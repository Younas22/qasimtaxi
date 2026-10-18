@props(['href', 'active' => false])

@php
    $classes = $active
        ? 'bg-luxury-gold/10 text-luxury-gold border-luxury-gold'
        : 'text-luxury-muted border-transparent hover:bg-luxury-graphite hover:text-luxury-white';
@endphp

<a href="{{ $href }}" {{ $attributes->merge([
    'class' => 'flex items-center gap-3 rounded-lg border-s-2 px-4 py-2.5 text-sm font-medium transition ' . $classes,
]) }}>
    {{ $slot }}
</a>
