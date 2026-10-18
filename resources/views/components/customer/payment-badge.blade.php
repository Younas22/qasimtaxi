@props(['status'])

@php
    $classes = match ($status) {
        'paid' => 'bg-emerald-500/10 text-emerald-400',
        'refunded' => 'bg-luxury-slate text-luxury-muted',
        default => 'bg-luxury-gold/10 text-luxury-gold',
    };
    $label = \App\Models\Booking::PAYMENT_STATUSES[$status] ?? ucfirst($status);
@endphp

<span {{ $attributes->merge(['class' => "rounded-full px-2.5 py-1 text-xs font-medium $classes"]) }}>
    {{ $label }}
</span>
