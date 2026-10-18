@php
    // Reviews lives in the mobile menu drawer instead of the bottom bar —
    // it's account/reputation info, not one of the driver's operational
    // tasks (availability, rides, earnings) the bottom bar is for.
    $tabs = [
        ['label' => __('Home'), 'route' => 'driver.dashboard', 'match' => 'driver.dashboard', 'icon' => 'home'],
        ['label' => __('My Rides'), 'route' => 'driver.bookings.index', 'match' => 'driver.bookings.*', 'icon' => 'car'],
        ['label' => __('Earnings'), 'route' => 'driver.earnings.index', 'match' => 'driver.earnings.*', 'icon' => 'cash'],
    ];
@endphp

<nav class="pb-safe fixed inset-x-0 bottom-0 z-30 border-t border-luxury-border bg-luxury-charcoal/95 backdrop-blur lg:hidden">
    <div class="grid grid-cols-4">
        @foreach ($tabs as $tab)
            @php $isActive = request()->routeIs($tab['match']); @endphp
            <a href="{{ route($tab['route']) }}" aria-current="{{ $isActive ? 'page' : 'false' }}"
                class="tap-scale relative flex min-h-[3.25rem] flex-col items-center justify-center gap-1 py-2.5 text-[11px] font-medium transition {{ $isActive ? 'text-luxury-gold' : 'text-luxury-muted' }}">
                <x-icon name="{{ $tab['icon'] }}" class="h-5 w-5" />
                {{ $tab['label'] }}
            </a>
        @endforeach

        <button type="button" @click="sidebarOpen = true" aria-label="{{ __('Open menu') }}"
            class="tap-scale flex min-h-[3.25rem] flex-col items-center justify-center gap-1 py-2.5 text-[11px] font-medium text-luxury-muted transition">
            <x-icon name="menu" class="h-5 w-5" />
            {{ __('Menu') }}
        </button>
    </div>
</nav>
