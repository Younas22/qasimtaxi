<x-driver.layouts.app :title="__('Dashboard')">
    @php
        $firstName = explode(' ', $driver->name)[0];
        $primaryRide = $activeRide ?? $upcomingRide;
        $navigateTarget = $primaryRide
            ? ($activeRide ? $primaryRide->dropoff_location : $primaryRide->pickup_location)
            : null;
        $navigateUrl = $navigateTarget
            ? 'https://www.google.com/maps/dir/?api=1&destination=' . urlencode($navigateTarget)
            : null;
        $hour = (int) now(setting('timezone', config('app.timezone')))->format('G');
        $greeting = $hour < 12 ? __('Good morning') : ($hour < 18 ? __('Good afternoon') : __('Good evening'));
    @endphp

    {{-- Greeting --}}
    <div class="mb-4">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ $greeting }}, {{ $firstName }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ __('Ready for your next ride?') }}</p>
    </div>

    {{-- Availability — the single most important control, so it gets its
         own large, unmistakable card (the same toggle also lives in the
         topbar for quick access from any screen). --}}
    <div class="mb-6 flex items-center justify-between gap-4 rounded-2xl border p-5 {{ $driver->is_online ? 'border-emerald-500/30 bg-emerald-500/5' : 'border-luxury-border bg-luxury-charcoal' }}">
        <div class="flex items-center gap-3">
            <span class="relative flex h-3 w-3 shrink-0">
                @if ($driver->is_online)
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                @endif
                <span class="relative inline-flex h-3 w-3 rounded-full {{ $driver->is_online ? 'bg-emerald-400' : 'bg-luxury-muted' }}"></span>
            </span>
            <div>
                <p class="text-base font-semibold {{ $driver->is_online ? 'text-emerald-400' : 'text-luxury-white' }}">
                    {{ $driver->is_online ? __('You are Online') : __('You are Offline') }}
                </p>
                <p class="text-xs text-luxury-muted">{{ $driver->is_online ? __('Ready to receive ride assignments.') : __('Go online to start receiving rides.') }}</p>
            </div>
        </div>
        <form method="POST" action="{{ route('driver.status.toggle') }}" class="shrink-0" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            <button type="submit" :disabled="submitting" role="switch" :aria-checked="@js((bool) $driver->is_online)" aria-label="{{ __('Toggle availability') }}"
                class="tap-scale inline-flex min-w-[8rem] items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-semibold transition disabled:opacity-60 {{ $driver->is_online ? 'border border-luxury-border text-luxury-muted hover:border-red-500/40 hover:text-red-400' : 'bg-luxury-gold text-luxury-black hover:bg-luxury-gold-light' }}">
                <svg x-show="submitting" x-cloak class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span x-text="submitting ? '{{ __('Updating…') }}' : '{{ $driver->is_online ? __('Go Offline') : __('Go Online') }}'"></span>
            </button>
        </form>
    </div>

    {{-- Your Ride — active (in-progress) takes priority over an assigned
         ride waiting to start; this is the driver's single point of focus
         when it applies, so it always renders before the stats. --}}
    @if ($primaryRide)
        <div class="mb-6 overflow-hidden rounded-2xl border {{ $activeRide ? 'border-blue-500/30 bg-blue-500/5' : 'border-luxury-gold/30 bg-gradient-to-br from-luxury-charcoal to-luxury-graphite' }}">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b {{ $activeRide ? 'border-blue-500/20' : 'border-luxury-border' }} px-5 py-3">
                <span class="text-xs font-semibold uppercase tracking-wide {{ $activeRide ? 'text-blue-400' : 'text-luxury-gold' }}">
                    {{ $activeRide ? __('Ride in Progress') : __('Up Next — Ready to Start') }}
                </span>
                @if ($activeRide?->estimated_arrival_at)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-500/10 px-3 py-1 text-xs font-semibold text-blue-400">
                        <x-icon name="clock" class="h-3.5 w-3.5" />
                        {{ __('Arriving :time', ['time' => $activeRide->estimated_arrival_at->format('h:i A')]) }}
                    </span>
                @else
                    <span class="text-xs text-luxury-muted">{{ $primaryRide->pickup_datetime->format('M d — h:i A') }}</span>
                @endif
            </div>

            <div class="p-5">
                {{-- Route --}}
                <div class="flex items-start gap-3">
                    <div class="mt-0.5 flex flex-col items-center">
                        <span class="h-2 w-2 shrink-0 rounded-full bg-luxury-gold"></span>
                        <span class="my-1 h-8 w-px bg-luxury-border"></span>
                        <x-icon name="map-pin" class="h-3.5 w-3.5 shrink-0 text-luxury-gold" />
                    </div>
                    <div class="min-w-0 flex-1 space-y-3">
                        <p class="truncate text-sm font-medium text-luxury-white">{{ $primaryRide->pickup_location }}</p>
                        <p class="truncate text-sm font-medium text-luxury-white">{{ $primaryRide->dropoff_location }}</p>
                    </div>
                </div>

                {{-- Passenger --}}
                <div class="mt-5 flex items-center gap-3 rounded-xl bg-luxury-graphite/40 p-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-luxury-gold/10 text-luxury-gold">
                        <x-icon name="user" class="h-5 w-5" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-luxury-white">{{ $primaryRide->customer?->name ?? __('Passenger') }}</p>
                        <p class="text-[11px] text-luxury-muted">{{ $primaryRide->booking_number }}</p>
                    </div>
                    @if ($primaryRide->customer?->phone)
                        <a href="tel:{{ $primaryRide->customer->phone }}" aria-label="{{ __('Call passenger') }}"
                            class="tap-scale flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-luxury-border text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                            <x-icon name="phone" class="h-4 w-4" />
                        </a>
                    @endif
                </div>

                {{-- Primary action(s) — exactly one contextual action per
                     status, plus Navigate, matching the actual ride state. --}}
                <div class="mt-5 grid grid-cols-2 gap-2 border-t border-luxury-border pt-5">
                    <a href="{{ $navigateUrl }}" target="_blank" rel="noopener"
                        class="tap-scale flex items-center justify-center gap-2 rounded-lg border border-luxury-border px-4 py-3 text-sm font-semibold text-luxury-white transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                        <x-icon name="map-pin" class="h-4 w-4" />
                        {{ __('Navigate') }}
                    </a>

                    @if ($activeRide)
                        <x-driver.ride-action-button :action="route('driver.bookings.complete', $activeRide)" :label="__('Complete Trip')" :loading-label="__('Completing…')" />
                    @else
                        <x-driver.ride-action-button :action="route('driver.bookings.start', $upcomingRide)" :label="__('Start Trip')" :loading-label="__('Starting…')" />
                    @endif
                </div>

                <a href="{{ route('driver.bookings.show', $primaryRide) }}" class="mt-3 block text-center text-xs font-medium text-luxury-muted hover:text-luxury-white">
                    {{ __('View Full Details') }}
                </a>
            </div>
        </div>
    @else
        <div class="mb-6 rounded-2xl border border-luxury-border bg-luxury-charcoal p-8 text-center">
            <x-icon name="car" class="mx-auto h-8 w-8 text-luxury-muted" />
            <p class="mt-3 text-sm font-medium text-luxury-white">{{ __('No Upcoming Ride') }}</p>
            <p class="mt-1 text-sm text-luxury-muted">{{ __("You don't have an assigned ride yet.") }}</p>
        </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 gap-3 sm:gap-5 md:grid-cols-4">
        <x-admin.stat-card :label="__('Today\'s Trips')" :value="$stats['todayTrips']">
            <x-icon name="car" class="h-5 w-5" />
        </x-admin.stat-card>

        <x-admin.stat-card :label="__('This Month\'s Earnings')" :value="currency($stats['monthEarnings'])">
            <x-icon name="cash" class="h-5 w-5" />
        </x-admin.stat-card>

        <x-admin.stat-card :label="__('Completed Trips')" :value="$stats['totalTrips']">
            <x-icon name="check-circle" class="h-5 w-5" />
        </x-admin.stat-card>

        <x-admin.stat-card :label="__('Average Rating')" :value="$stats['averageRating'] ?? __('N/A')">
            <x-icon name="star" class="h-5 w-5" />
        </x-admin.stat-card>
    </div>

    {{-- Today's Trips — cards, not a table, so they read the same on a
         phone as on a desktop. --}}
    <div class="mt-8">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-luxury-white">{{ __("Today's Trips") }}</h3>
            <a href="{{ route('driver.bookings.index') }}" class="text-xs font-medium text-luxury-gold hover:text-luxury-gold-light">{{ __('View All') }}</a>
        </div>

        @if ($todayBookings->isNotEmpty())
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                @foreach ($todayBookings as $booking)
                    <a href="{{ route('driver.bookings.show', $booking) }}"
                        class="tap-scale flex items-center gap-4 rounded-2xl border border-luxury-border bg-luxury-charcoal p-4 transition hover:border-luxury-gold/40">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-luxury-gold/10 text-luxury-gold">
                            <x-icon name="car" class="h-5 w-5" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <p class="truncate text-sm font-medium text-luxury-white">{{ $booking->customer?->name ?? $booking->booking_number }}</p>
                                <x-customer.status-badge :status="$booking->status" />
                            </div>
                            <p class="mt-1 truncate text-xs text-luxury-muted">{{ $booking->pickup_location }} &rarr; {{ $booking->dropoff_location }}</p>
                            <p class="mt-0.5 text-[11px] text-luxury-muted">{{ $booking->pickup_datetime->format('h:i A') }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-10 text-center">
                <x-icon name="calendar" class="mx-auto h-8 w-8 text-luxury-muted" />
                <p class="mt-3 text-sm text-luxury-muted">{{ __('No trips scheduled for today.') }}</p>
            </div>
        @endif
    </div>
</x-driver.layouts.app>
