@php
    $admin = auth()->guard('admin')->user();
    $tabs = [
        ['label' => __('Home'), 'route' => 'admin.dashboard', 'match' => 'admin.dashboard', 'icon' => 'grid', 'permission' => null],
        ['label' => __('Bookings'), 'route' => 'admin.bookings.index', 'match' => 'admin.bookings.*', 'icon' => 'calendar', 'permission' => 'bookings.view'],
        ['label' => __('Dispatch'), 'route' => 'admin.fleet.index', 'match' => 'admin.fleet.*', 'icon' => 'car', 'permission' => 'drivers.view'],
        ['label' => __('Reports'), 'route' => 'admin.reports.index', 'match' => 'admin.reports.*', 'icon' => 'bar-chart', 'permission' => 'reports.view'],
    ];
    $icons = [
        'grid' => 'M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM14 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z',
        'calendar' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
        'car' => 'M8 17a2 2 0 11-4 0 2 2 0 014 0zM20 17a2 2 0 11-4 0 2 2 0 014 0zM6 17H4v-4l1.5-4.5A2 2 0 017.4 7h9.2a2 2 0 011.9 1.5L20 13v4h-2m-12 0h8m-8 0H4',
        'bar-chart' => 'M3 13.125c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z',
        'menu' => 'M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5',
    ];

    $visibleTabs = collect($tabs)->filter(fn ($tab) => ! $tab['permission'] || $admin?->hasPermission($tab['permission']))->values();
@endphp

<nav class="pb-safe fixed inset-x-0 bottom-0 z-30 border-t border-luxury-border bg-luxury-charcoal/95 backdrop-blur lg:hidden">
    <div class="grid" style="grid-template-columns: repeat({{ $visibleTabs->count() + 1 }}, minmax(0, 1fr));">
        @foreach ($visibleTabs as $tab)
            @php $isActive = request()->routeIs($tab['match']); @endphp
            <a href="{{ route($tab['route']) }}" aria-current="{{ $isActive ? 'page' : 'false' }}"
                class="tap-scale flex min-h-[3.25rem] flex-col items-center justify-center gap-1 py-2.5 text-[11px] font-medium transition {{ $isActive ? 'text-luxury-gold' : 'text-luxury-muted' }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$tab['icon']] }}" />
                </svg>
                {{ $tab['label'] }}
            </a>
        @endforeach

        <button type="button" @click="sidebarOpen = true" aria-label="{{ __('Open menu') }}"
            class="tap-scale flex min-h-[3.25rem] flex-col items-center justify-center gap-1 py-2.5 text-[11px] font-medium text-luxury-muted transition">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['menu'] }}" />
            </svg>
            {{ __('Menu') }}
        </button>
    </div>
</nav>
