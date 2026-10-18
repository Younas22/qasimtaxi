@props(['status'])

@php
    $status = $status ?? 'not_applicable';
    $classes = match ($status) {
        'refunded' => 'bg-emerald-500/10 text-emerald-400',
        'rejected' => 'bg-red-500/10 text-red-400',
        'pending', 'processing' => 'bg-luxury-gold/10 text-luxury-gold',
        default => 'bg-luxury-slate text-luxury-muted',
    };
    $label = \App\Models\Booking::REFUND_STATUSES[$status] ?? ucfirst($status);
@endphp

<span {{ $attributes->merge(['class' => "rounded-full px-2.5 py-1 text-xs font-medium $classes"]) }}>
    {{ $label }}
</span>
