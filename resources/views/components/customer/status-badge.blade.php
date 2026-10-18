@props(['status'])

@php
    $classes = match ($status) {
        'completed' => 'bg-emerald-500/10 text-emerald-400',
        'cancelled' => 'bg-red-500/10 text-red-400',
        'in_progress' => 'bg-blue-500/10 text-blue-400',
        'confirmed', 'assigned' => 'bg-luxury-gold/10 text-luxury-gold',
        default => 'bg-luxury-slate text-luxury-muted',
    };
    // Customer-facing wording differs slightly from the admin panel's
    // internal status names — "assigned" (a driver has been matched) reads
    // more naturally to a rider as "On the Way", and "in_progress" (driver
    // has pressed Start Ride) is clearer as "Ride in Progress".
    $label = match ($status) {
        'assigned' => __('On the Way'),
        'in_progress' => __('Ride in Progress'),
        default => \App\Models\Booking::STATUSES[$status] ?? ucfirst($status),
    };
@endphp

<span {{ $attributes->merge(['class' => "rounded-full px-2.5 py-1 text-xs font-medium $classes"]) }}>
    {{ $label }}
</span>
