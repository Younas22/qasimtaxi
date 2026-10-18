@props(['type' => 'submit', 'variant' => 'primary'])

@php
    $variants = [
        'primary' => 'bg-luxury-gold text-luxury-black hover:bg-luxury-gold-light focus:ring-luxury-gold',
        'secondary' => 'bg-luxury-graphite text-luxury-white border border-luxury-border hover:border-luxury-secondary hover:text-luxury-secondary focus:ring-luxury-secondary',
        'ghost' => 'bg-transparent text-luxury-muted hover:text-luxury-white focus:ring-luxury-gold',
        'danger' => 'bg-red-500/10 text-red-400 border border-red-500/30 hover:bg-red-500/20 focus:ring-red-500',
    ];
@endphp

<button type="{{ $type }}" {{ $attributes->merge([
    'class' => 'inline-flex items-center justify-center gap-2 rounded-lg px-5 py-3 text-sm font-semibold tracking-wide transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-luxury-black disabled:opacity-50 disabled:cursor-not-allowed '
        . ($variants[$variant] ?? $variants['primary']),
]) }}>
    {{ $slot }}
</button>
