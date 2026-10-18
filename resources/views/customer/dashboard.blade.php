<x-customer.layouts.app :title="__('Dashboard')">
    @php
        $firstName = explode(' ', auth()->guard('customer')->user()->name)[0];
        $hour = (int) now(setting('timezone', config('app.timezone')))->format('G');
        $greeting = $hour < 12 ? __('Good morning') : ($hour < 18 ? __('Good afternoon') : __('Good evening'));

        $quickActions = [
            ['label' => __('Book a Ride'), 'route' => 'customer.bookings.create', 'icon' => 'plus'],
            ['label' => __('My Trips'), 'route' => 'customer.bookings.index', 'icon' => 'car'],
            ['label' => __('Addresses'), 'route' => 'customer.addresses.index', 'icon' => 'map-pin'],
            ['label' => __('Favorites'), 'route' => 'customer.favorites.index', 'icon' => 'heart'],
            ['label' => __('Wallet'), 'route' => 'customer.wallet.index', 'icon' => 'wallet'],
        ];
    @endphp

    {{-- Greeting + Primary CTA --}}
    <div class="mb-6 flex flex-col gap-4 rounded-2xl border border-luxury-gold/30 bg-gradient-to-br from-luxury-charcoal to-luxury-graphite p-6 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-medium uppercase tracking-wider text-luxury-gold">{{ $greeting }}</p>
            <h2 class="mt-1 text-2xl font-semibold text-luxury-white">{{ $firstName }}</h2>
            <p class="mt-1 text-sm text-luxury-muted">{{ __("Here's what's happening with your rides.") }}</p>
        </div>
        <a href="{{ route('customer.bookings.create') }}" class="shrink-0">
            <x-admin.button type="button" variant="primary" class="w-full sm:w-auto">
                <x-icon name="car" class="h-4 w-4" />
                {{ __('Book a Ride') }}
            </x-admin.button>
        </a>
    </div>

    {{-- Quick Actions --}}
    <div class="mb-6 grid grid-cols-4 gap-2 sm:grid-cols-5 sm:gap-3">
        @foreach ($quickActions as $action)
            <a href="{{ route($action['route']) }}"
                class="tap-scale flex flex-col items-center gap-2 rounded-2xl border border-luxury-border bg-luxury-charcoal px-2 py-3 text-center transition hover:border-luxury-gold/40">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-luxury-gold/10 text-luxury-gold">
                    <x-icon name="{{ $action['icon'] }}" class="h-4 w-4" />
                </span>
                <span class="truncate text-[11px] font-medium leading-tight text-luxury-muted">{{ $action['label'] }}</span>
            </a>
        @endforeach
    </div>

    {{-- Your Ride --}}
    @if ($nextRide)
        <div class="mb-6 overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal"
            x-data="countdownTimer('{{ $nextRide->pickup_datetime->toIso8601String() }}')" x-init="if (@js($nextRide->status !== 'in_progress')) start()">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-luxury-border bg-luxury-graphite/40 px-5 py-3">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold uppercase tracking-wide text-luxury-gold">
                        {{ $nextRide->status === 'in_progress' ? __('Your Ride') : __('Your Next Ride') }}
                    </span>
                    <x-customer.status-badge :status="$nextRide->status" />
                </div>

                @if ($nextRide->status === 'in_progress')
                    @if ($nextRide->estimated_arrival_at)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-luxury-gold/10 px-3 py-1 text-xs font-semibold text-luxury-gold">
                            <x-icon name="clock" class="h-3.5 w-3.5" />
                            {{ __('Arriving :time', ['time' => $nextRide->estimated_arrival_at->format('h:i A')]) }}
                        </span>
                    @endif
                @elseif ($nextRideDispatch && $nextRideDispatch['duration_minutes'] !== null)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-luxury-gold/10 px-3 py-1 text-xs font-semibold text-luxury-gold">
                        <x-icon name="car" class="h-3.5 w-3.5" />
                        {{ __('Driver ETA: :min min', ['min' => $nextRideDispatch['duration_minutes']]) }}
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-luxury-gold/10 px-3 py-1 text-xs font-semibold text-luxury-gold" :class="{ 'animate-pulse': isSoon }">
                        <x-icon name="clock" class="h-3.5 w-3.5" />
                        <span x-text="label"></span>
                    </span>
                @endif
            </div>

            <div class="p-5">
                <div class="flex items-start gap-3">
                    <div class="mt-0.5 flex flex-col items-center">
                        <span class="h-2 w-2 shrink-0 rounded-full bg-luxury-gold"></span>
                        <span class="my-1 h-8 w-px bg-luxury-border"></span>
                        <x-icon name="map-pin" class="h-3.5 w-3.5 shrink-0 text-luxury-gold" />
                    </div>
                    <div class="min-w-0 flex-1 space-y-3">
                        <p class="truncate text-sm font-medium text-luxury-white">{{ $nextRide->pickup_location }}</p>
                        <p class="truncate text-sm font-medium text-luxury-white">{{ $nextRide->dropoff_location }}</p>
                    </div>
                    <p class="shrink-0 text-end text-xs text-luxury-muted">{{ $nextRide->pickup_datetime->format('M d, Y — h:i A') }}</p>
                </div>

                <div class="mt-5 grid grid-cols-1 gap-3 border-t border-luxury-border pt-5 sm:grid-cols-2">
                    <div class="flex items-center gap-3 rounded-xl bg-luxury-graphite/40 p-3">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-full border border-luxury-border bg-luxury-graphite">
                            @if ($nextRide->driver?->photo_url)
                                <x-lazy-image :src="$nextRide->driver->photo_url" :alt="$nextRide->driver->name" />
                            @else
                                <x-icon name="user" class="h-5 w-5 text-luxury-muted" />
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-xs font-medium text-luxury-white">{{ $nextRide->driver?->name ?? __('Not yet assigned') }}</p>
                            @if ($nextRide->driver?->average_rating)
                                <p class="flex items-center gap-1 text-[11px] text-luxury-muted">
                                    <x-icon name="star" class="h-3 w-3 text-luxury-gold" /> {{ $nextRide->driver->average_rating }}
                                </p>
                            @endif
                        </div>
                        @if ($nextRide->driver?->phone)
                            <a href="tel:{{ $nextRide->driver->phone }}" aria-label="{{ __('Call driver') }}"
                                class="tap-scale flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-luxury-border text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                <x-icon name="phone" class="h-3.5 w-3.5" />
                            </a>
                        @endif
                    </div>

                    <div class="flex items-center gap-3 rounded-xl bg-luxury-graphite/40 p-3">
                        <div class="h-11 w-11 shrink-0 overflow-hidden rounded-lg border border-luxury-border bg-luxury-graphite">
                            @if ($nextRide->vehicle?->image_url)
                                <x-lazy-image :src="$nextRide->vehicle->image_url" :alt="$nextRide->vehicle->name" />
                            @else
                                <div class="flex h-full w-full items-center justify-center">
                                    <x-icon name="car" class="h-5 w-5 text-luxury-muted" />
                                </div>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-xs font-medium text-luxury-white">{{ $nextRide->vehicle?->category?->name ?? $nextRide->vehicle?->name ?? '—' }}</p>
                            @if ($nextRide->vehicle?->plate_number)
                                <p class="text-[11px] uppercase tracking-wide text-luxury-muted">{{ $nextRide->vehicle->plate_number }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-luxury-border pt-5">
                    <div>
                        <p class="text-[11px] uppercase tracking-wide text-luxury-muted">{{ __('Fare') }}</p>
                        <p class="text-lg font-semibold text-luxury-gold">{{ currency($nextRide->fare_amount) }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        @if ($nextRide->driver?->phone)
                            <a href="tel:{{ $nextRide->driver->phone }}"
                                class="tap-scale inline-flex items-center gap-1.5 rounded-lg border border-luxury-border px-4 py-2 text-xs font-semibold text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                <x-icon name="phone" class="h-3.5 w-3.5" />
                                {{ __('Call Driver') }}
                            </a>
                        @endif
                        <a href="{{ route('customer.bookings.show', $nextRide) }}"
                            class="tap-scale inline-flex items-center gap-1.5 rounded-lg bg-luxury-gold px-4 py-2 text-xs font-semibold text-luxury-black transition hover:bg-luxury-gold-light">
                            {{ in_array($nextRide->status, ['confirmed', 'assigned', 'in_progress'], true) ? __('Track Ride') : __('View Booking') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 gap-3 sm:gap-5 md:grid-cols-4">
        <x-admin.stat-card :label="__('Total Spent')" :value="currency($stats['totalSpent'])">
            <x-icon name="cash" class="h-5 w-5" />
        </x-admin.stat-card>

        <x-admin.stat-card :label="__('Completed Trips')" :value="$stats['completed']">
            <x-icon name="check-circle" class="h-5 w-5" />
        </x-admin.stat-card>

        <x-admin.stat-card :label="__('Upcoming Trips')" :value="$stats['upcoming']">
            <x-icon name="clock" class="h-5 w-5" />
        </x-admin.stat-card>

        <x-admin.stat-card :label="__('Total Bookings')" :value="$stats['total']">
            <x-icon name="car" class="h-5 w-5" />
        </x-admin.stat-card>
    </div>

    {{--
        Explore Our Fleet + Favorite Vehicles — temporarily hidden from the
        dashboard. $recommendedVehicles/$favoriteVehicles are still passed
        in from DashboardController if this needs restoring later.

    <div class="mt-8">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-luxury-white">{{ __('Explore Our Fleet') }}</h3>
            <a href="{{ route('pages.show', 'services') }}" class="text-xs font-medium text-luxury-gold hover:text-luxury-gold-light">{{ __('View All') }}</a>
        </div>
        <div class="scrollbar-luxury -mx-1 flex gap-4 overflow-x-auto px-1 pb-2">
            @foreach ($recommendedVehicles as $vehicle)
                <div class="w-64 shrink-0">
                    <x-vehicle-card :vehicle="$vehicle" />
                </div>
            @endforeach
        </div>
    </div>

    <div class="mt-8">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-luxury-white">{{ __('Favorite Vehicles') }}</h3>
            @if ($favoriteVehicles->isNotEmpty())
                <a href="{{ route('customer.favorites.index') }}" class="text-xs font-medium text-luxury-gold hover:text-luxury-gold-light">{{ __('View All') }}</a>
            @endif
        </div>

        @if ($favoriteVehicles->isNotEmpty())
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($favoriteVehicles as $vehicle)
                    <x-vehicle-card :vehicle="$vehicle" />
                @endforeach
            </div>
        @else
            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-8 text-center">
                <x-icon name="heart" class="mx-auto h-8 w-8 text-luxury-muted" />
                <p class="mt-3 text-sm text-luxury-muted">{{ __("You haven't favorited any vehicles yet.") }}</p>
                <a href="{{ route('pages.show', 'services') }}" class="mt-3 inline-flex items-center gap-1.5 text-sm font-semibold text-luxury-gold hover:text-luxury-gold-light">
                    {{ __('Browse our fleet') }}
                    <x-icon name="chevron-right" class="h-4 w-4 rtl:rotate-180" />
                </a>
            </div>
        @endif
    </div>
    --}}

    {{-- Recent Trips — cards, not a table, so they read the same on a
         phone as on a desktop and never need horizontal scrolling. --}}
    <div class="mt-8">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-luxury-white">{{ __('Recent Trips') }}</h3>
            <a href="{{ route('customer.bookings.index') }}" class="text-xs font-medium text-luxury-gold hover:text-luxury-gold-light">{{ __('View All') }}</a>
        </div>

        @if ($recentBookings->isNotEmpty())
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                @foreach ($recentBookings as $booking)
                    <a href="{{ route('customer.bookings.show', $booking) }}"
                        class="tap-scale flex items-center gap-4 rounded-2xl border border-luxury-border bg-luxury-charcoal p-4 transition hover:border-luxury-gold/40">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-luxury-gold/10 text-luxury-gold">
                            <x-icon name="car" class="h-5 w-5" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <p class="truncate text-sm font-medium text-luxury-white">{{ $booking->booking_number }}</p>
                                <x-customer.status-badge :status="$booking->status" />
                            </div>
                            <p class="mt-1 truncate text-xs text-luxury-muted">{{ $booking->pickup_location }} &rarr; {{ $booking->dropoff_location }}</p>
                            <p class="mt-0.5 text-[11px] text-luxury-muted">{{ $booking->pickup_datetime->format('M d, Y') }}</p>
                        </div>
                        <p class="shrink-0 text-sm font-semibold text-luxury-gold">{{ currency($booking->fare_amount) }}</p>
                    </a>
                @endforeach
            </div>
        @else
            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-10 text-center">
                <x-icon name="car" class="mx-auto h-8 w-8 text-luxury-muted" />
                <p class="mt-3 text-sm text-luxury-muted">{{ __("You haven't made any bookings yet.") }}</p>
                <a href="{{ route('customer.bookings.create') }}" class="mt-3 inline-flex items-center gap-1.5 text-sm font-semibold text-luxury-gold hover:text-luxury-gold-light">
                    {{ __('Book your first ride') }}
                    <x-icon name="chevron-right" class="h-4 w-4 rtl:rotate-180" />
                </a>
            </div>
        @endif
    </div>

    @if ($nextRide)
        <script>
            function countdownTimer(targetIso) {
                const prefix = @json(__('in'));
                const startingNow = @json(__('Starting now'));

                return {
                    label: '',
                    isSoon: false,
                    timer: null,

                    start() {
                        this.tick();
                        this.timer = setInterval(() => this.tick(), 30000);
                    },

                    tick() {
                        const diffMs = new Date(targetIso).getTime() - Date.now();

                        if (diffMs <= 0) {
                            this.label = startingNow;
                            this.isSoon = true;
                            clearInterval(this.timer);
                            return;
                        }

                        const minutes = Math.floor(diffMs / 60000);
                        const hours = Math.floor(minutes / 60);
                        const days = Math.floor(hours / 24);

                        this.isSoon = minutes <= 60;

                        if (days >= 1) {
                            this.label = `${prefix} ${days}d ${hours % 24}h`;
                        } else if (hours >= 1) {
                            this.label = `${prefix} ${hours}h ${minutes % 60}m`;
                        } else {
                            this.label = `${prefix} ${minutes}m`;
                        }
                    },
                };
            }
        </script>
    @endif
</x-customer.layouts.app>
