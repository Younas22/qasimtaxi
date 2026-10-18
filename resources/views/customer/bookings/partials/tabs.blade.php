{{-- Included with @include('customer.bookings.partials.tabs', ['active' => 'all']) — $active is one of: all, upcoming, completed, cancelled. --}}
@php
    $tabs = [
        'all' => ['label' => __('All'), 'route' => 'customer.bookings.index'],
        'upcoming' => ['label' => __('Upcoming'), 'route' => 'customer.bookings.upcoming'],
        'completed' => ['label' => __('Completed'), 'route' => 'customer.bookings.completed'],
        'cancelled' => ['label' => __('Cancelled'), 'route' => 'customer.bookings.cancelled'],
    ];
@endphp

<div class="scrollbar-luxury mb-6 flex gap-1.5 overflow-x-auto rounded-xl border border-luxury-border bg-luxury-charcoal p-1.5">
    @foreach ($tabs as $key => $tab)
        <a href="{{ route($tab['route']) }}"
            class="tap-scale flex-1 whitespace-nowrap rounded-lg px-3 py-2 text-center text-xs font-semibold transition {{ $active === $key ? 'bg-luxury-gold text-luxury-black' : 'text-luxury-muted hover:text-luxury-white' }}">
            {{ $tab['label'] }}
        </a>
    @endforeach
</div>
