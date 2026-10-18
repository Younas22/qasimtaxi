@php
    $tabs = [
        ['label' => __('Home'), 'route' => 'customer.dashboard', 'match' => 'customer.dashboard', 'icon' => 'home'],
        ['label' => __('Trips'), 'route' => 'customer.bookings.index', 'match' => ['customer.bookings.index', 'customer.bookings.upcoming', 'customer.bookings.completed', 'customer.bookings.cancelled', 'customer.bookings.show'], 'icon' => 'car'],
    ];
    $tabsEnd = [
        ['label' => __('Wallet'), 'route' => 'customer.wallet.index', 'match' => 'customer.wallet.*', 'icon' => 'wallet'],
    ];
@endphp

<nav class="pb-safe fixed inset-x-0 bottom-0 z-30 border-t border-luxury-border bg-luxury-charcoal/95 backdrop-blur lg:hidden">
    <div class="grid grid-cols-5">
        @foreach ($tabs as $tab)
            @php $isActive = request()->routeIs($tab['match']); @endphp
            <a href="{{ route($tab['route']) }}" aria-current="{{ $isActive ? 'page' : 'false' }}"
                class="tap-scale flex min-h-[3.25rem] flex-col items-center justify-center gap-1 py-2.5 text-[11px] font-medium transition {{ $isActive ? 'text-luxury-gold' : 'text-luxury-muted' }}">
                <x-icon name="{{ $tab['icon'] }}" class="h-5 w-5" />
                {{ $tab['label'] }}
            </a>
        @endforeach

        {{-- Book — the primary action, raised above the bar so it reads as
             the one thing this app most wants you to do. --}}
        <div class="flex items-center justify-center">
            <a href="{{ route('customer.bookings.create') }}" aria-label="{{ __('Book a Ride') }}"
                class="tap-scale -mt-7 flex h-14 w-14 items-center justify-center rounded-full border-4 border-luxury-charcoal bg-luxury-gold text-luxury-black shadow-lg shadow-luxury-gold/30 transition hover:bg-luxury-gold-light">
                <x-icon name="plus" class="h-6 w-6" />
            </a>
        </div>

        @foreach ($tabsEnd as $tab)
            @php $isActive = request()->routeIs($tab['match']); @endphp
            <a href="{{ route($tab['route']) }}" aria-current="{{ $isActive ? 'page' : 'false' }}"
                class="tap-scale flex min-h-[3.25rem] flex-col items-center justify-center gap-1 py-2.5 text-[11px] font-medium transition {{ $isActive ? 'text-luxury-gold' : 'text-luxury-muted' }}">
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
