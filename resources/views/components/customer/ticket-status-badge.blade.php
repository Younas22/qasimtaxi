@props(['status'])

@php
    $classes = match ($status) {
        'open' => 'bg-luxury-gold/10 text-luxury-gold',
        'in_progress' => 'bg-luxury-secondary/10 text-luxury-secondary',
        'closed' => 'bg-luxury-slate text-luxury-muted',
        default => 'bg-luxury-slate text-luxury-muted',
    };
    $label = \App\Models\SupportTicket::STATUSES[$status] ?? ucfirst($status);
@endphp

<span {{ $attributes->merge(['class' => "rounded-full px-2.5 py-1 text-xs font-medium $classes"]) }}>
    {{ $label }}
</span>
