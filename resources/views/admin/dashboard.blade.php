<x-admin.layouts.app :title="__('Dashboard')">
    @php
        $quickActions = [];
        if (\Illuminate\Support\Facades\Route::has('admin.bookings.create') && auth()->guard('admin')->user()?->hasPermission('bookings.create')) {
            $quickActions[] = ['label' => __('New Booking'), 'route' => 'admin.bookings.create', 'icon' => 'calendar'];
        }
        if (auth()->guard('admin')->user()?->hasPermission('drivers.view')) {
            $quickActions[] = ['label' => __('Dispatch'), 'route' => 'admin.fleet.index', 'icon' => 'car'];
        }
        if (auth()->guard('admin')->user()?->hasPermission('drivers.create')) {
            $quickActions[] = ['label' => __('Add Driver'), 'route' => 'admin.drivers.create', 'icon' => 'id'];
        }
        if (auth()->guard('admin')->user()?->hasPermission('customers.create')) {
            $quickActions[] = ['label' => __('Add Customer'), 'route' => 'admin.customers.create', 'icon' => 'users'];
        }
        if (auth()->guard('admin')->user()?->hasPermission('vehicles.create')) {
            $quickActions[] = ['label' => __('Add Vehicle'), 'route' => 'admin.vehicles.create', 'icon' => 'car'];
        }
        if (auth()->guard('admin')->user()?->hasPermission('reports.view')) {
            $quickActions[] = ['label' => __('Reports'), 'route' => 'admin.reports.index', 'icon' => 'bar-chart'];
        }

        $quickIcons = [
            'calendar' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
            'car' => 'M8 17a2 2 0 11-4 0 2 2 0 014 0zM20 17a2 2 0 11-4 0 2 2 0 014 0zM6 17H4v-4l1.5-4.5A2 2 0 017.4 7h9.2a2 2 0 011.9 1.5L20 13v4h-2m-12 0h8m-8 0H4',
            'id' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 10-4-4',
            'users' => 'M17 20h5v-2a3 3 0 00-5.36-1.86M9 20H4v-2a3 3 0 015.36-1.86m0 0a4 4 0 116.36 0M7 10a4 4 0 118 0 4 4 0 01-8 0z',
            'bar-chart' => 'M3 13.125c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z',
        ];
    @endphp

    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Overview') }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ __("Welcome back, :name. Here's what's happening today.", ['name' => auth()->guard('admin')->user()->name]) }}</p>
    </div>

    {{-- Quick Actions --}}
    @if (! empty($quickActions))
        <div class="mb-6 grid grid-cols-3 gap-2 sm:grid-cols-6 sm:gap-3">
            @foreach ($quickActions as $action)
                <a href="{{ route($action['route']) }}"
                    class="tap-scale flex flex-col items-center gap-2 rounded-2xl border border-luxury-border bg-luxury-charcoal px-2 py-3 text-center transition hover:border-luxury-gold/40">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-luxury-gold/10 text-luxury-gold">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $quickIcons[$action['icon']] }}" />
                        </svg>
                    </span>
                    <span class="truncate text-[11px] font-medium leading-tight text-luxury-muted">{{ $action['label'] }}</span>
                </a>
            @endforeach
        </div>
    @endif

    {{-- Right Now — the things that actually need attention today. --}}
    <div class="mb-6 grid grid-cols-2 gap-3 sm:gap-5 lg:grid-cols-4">
        <div class="rounded-2xl border border-blue-500/30 bg-blue-500/5 p-5">
            <p class="text-xs font-medium uppercase tracking-wider text-blue-400">{{ __('Active Rides') }}</p>
            <p class="mt-2 text-2xl font-semibold text-luxury-white sm:text-3xl">{{ number_format($activeRides) }}</p>
        </div>
        <div class="rounded-2xl border p-5 {{ $pendingBookings > 0 ? 'border-luxury-gold/30 bg-luxury-gold/5' : 'border-luxury-border bg-luxury-charcoal' }}">
            <p class="text-xs font-medium uppercase tracking-wider {{ $pendingBookings > 0 ? 'text-luxury-gold' : 'text-luxury-muted' }}">{{ __('Pending Bookings') }}</p>
            <p class="mt-2 text-2xl font-semibold text-luxury-white sm:text-3xl">{{ number_format($pendingBookings) }}</p>
        </div>
        <div class="rounded-2xl border p-5 {{ $unassignedBookings > 0 ? 'border-red-500/30 bg-red-500/5' : 'border-luxury-border bg-luxury-charcoal' }}">
            <p class="text-xs font-medium uppercase tracking-wider {{ $unassignedBookings > 0 ? 'text-red-400' : 'text-luxury-muted' }}">{{ __('Unassigned') }}</p>
            <p class="mt-2 text-2xl font-semibold text-luxury-white sm:text-3xl">{{ number_format($unassignedBookings) }}</p>
        </div>
        <div class="rounded-2xl border border-luxury-gold/30 bg-gradient-to-br from-luxury-charcoal to-luxury-graphite p-5">
            <p class="text-xs font-medium uppercase tracking-wider text-luxury-gold">{{ __("Today's Revenue") }}</p>
            <p class="mt-2 text-2xl font-semibold text-luxury-white sm:text-3xl">{{ currency($todayRevenue) }}</p>
            <p class="mt-0.5 text-[11px] text-luxury-muted">{{ __(':count bookings today', ['count' => $todayBookings]) }}</p>
        </div>
    </div>

    {{-- All-time totals --}}
    <div class="grid grid-cols-2 gap-3 sm:gap-5 sm:grid-cols-3 xl:grid-cols-5">
        <x-admin.stat-card :label="__('Bookings')" :value="number_format($stats['bookings'])">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        </x-admin.stat-card>

        <x-admin.stat-card :label="__('Revenue')" :value="currency($stats['revenue'])">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-4-4.5c0 1.38 1.79 2.5 4 2.5s4-1.12 4-2.5-1.79-2.5-4-2.5-4-1.12-4-2.5S9.79 6 12 6s4 1.12 4 2.5" />
            </svg>
        </x-admin.stat-card>

        <x-admin.stat-card :label="__('Vehicles')" :value="number_format($stats['vehicles'])">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 17a2 2 0 11-4 0 2 2 0 014 0zM20 17a2 2 0 11-4 0 2 2 0 014 0zM6 17H4v-4l1.5-4.5A2 2 0 017.4 7h9.2a2 2 0 011.9 1.5L20 13v4h-2m-12 0h8m-8 0H4" />
            </svg>
        </x-admin.stat-card>

        <x-admin.stat-card :label="__('Drivers')" :value="number_format($stats['drivers'])">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4z" />
            </svg>
        </x-admin.stat-card>

        <x-admin.stat-card :label="__('Customers')" :value="number_format($stats['customers'])">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.36-1.86M9 20H4v-2a3 3 0 015.36-1.86m0 0a4 4 0 116.36 0M7 10a4 4 0 118 0 4 4 0 01-8 0z" />
            </svg>
        </x-admin.stat-card>
    </div>

    <div class="mt-8 overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal">
        <div class="flex items-center justify-between border-b border-luxury-border px-6 py-4">
            <div>
                <h3 class="text-sm font-semibold text-luxury-white">{{ __('Live Fleet') }}</h3>
                <p class="mt-0.5 text-xs text-luxury-muted">
                    {{ __(':online drivers online, :busy currently on a trip.', ['online' => $fleetSummary['online'], 'busy' => $fleetSummary['busy']]) }}
                </p>
            </div>
            <a href="{{ route('admin.fleet.index') }}" class="shrink-0 text-xs font-medium text-luxury-gold hover:text-luxury-gold-light">{{ __('View Live Fleet') }}</a>
        </div>
    </div>

    <div class="mt-4 overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal">
        <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-4">
            <div>
                <h3 class="text-sm font-semibold text-luxury-white">{{ __('Browser Push') }}</h3>
                <p class="mt-0.5 text-xs text-luxury-muted">
                    {{ __('Status') }}: <span class="{{ $pushStatus->push_notifications_enabled ? 'text-emerald-400' : 'text-luxury-muted' }}">{{ $pushStatus->push_notifications_enabled ? __('Enabled') : __('Disabled') }}</span>
                    &middot; {{ __('Admin') }}: {{ $pushStatus->push_admin_enabled ? __('Enabled') : __('Disabled') }}
                    &middot; {{ __('Customers') }}: {{ $pushStatus->push_customer_enabled ? __('Enabled') : __('Disabled') }}
                    &middot; {{ __('Drivers') }}: {{ $pushStatus->push_driver_enabled ? __('Enabled') : __('Disabled') }}
                    &middot; {{ __('Active Subscriptions') }}: {{ number_format($pushSubscriptionCount) }}
                </p>
            </div>
            <a href="{{ route('admin.notification-settings.edit') }}" class="shrink-0 text-xs font-medium text-luxury-gold hover:text-luxury-gold-light">{{ __('Manage') }}</a>
        </div>
    </div>

    {{-- Recent Bookings — cards, not a table, so they read the same on a
         phone as on a desktop. --}}
    <div class="mt-8">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-luxury-white">{{ __('Recent Bookings') }}</h3>
            <a href="{{ route('admin.bookings.index') }}" class="text-xs font-medium text-luxury-gold hover:text-luxury-gold-light">{{ __('View All') }}</a>
        </div>

        @if ($recentBookings->isNotEmpty())
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                @foreach ($recentBookings as $booking)
                    <a href="{{ route('admin.bookings.show', $booking) }}"
                        class="tap-scale flex items-center gap-4 rounded-2xl border border-luxury-border bg-luxury-charcoal p-4 transition hover:border-luxury-gold/40">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-luxury-gold/10 text-luxury-gold">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 17a2 2 0 11-4 0 2 2 0 014 0zM20 17a2 2 0 11-4 0 2 2 0 014 0zM6 17H4v-4l1.5-4.5A2 2 0 017.4 7h9.2a2 2 0 011.9 1.5L20 13v4h-2m-12 0h8m-8 0H4" />
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <p class="truncate text-sm font-medium text-luxury-white">{{ $booking->booking_number }}</p>
                                <span class="shrink-0 rounded-full bg-luxury-gold/10 px-2 py-0.5 text-[10px] font-medium capitalize text-luxury-gold">{{ $booking->status }}</span>
                            </div>
                            <p class="mt-1 truncate text-xs text-luxury-muted">{{ $booking->customer?->name ?? '—' }} &middot; {{ $booking->driver?->name ?? __('Unassigned') }}</p>
                            <p class="mt-0.5 text-[11px] text-luxury-muted">{{ $booking->pickup_datetime->format('M d, Y H:i') }}</p>
                        </div>
                        <p class="shrink-0 text-sm font-semibold text-luxury-gold">{{ currency($booking->fare_amount) }}</p>
                    </a>
                @endforeach
            </div>
        @else
            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-10 text-center text-sm text-luxury-muted">
                {{ __('No bookings yet.') }}
            </div>
        @endif
    </div>
</x-admin.layouts.app>
