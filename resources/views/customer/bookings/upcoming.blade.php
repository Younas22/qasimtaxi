<x-customer.layouts.app :title="__('Upcoming Trips')">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Upcoming Trips') }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ __('Your scheduled rides, driver, and vehicle details.') }}</p>
    </div>

    @include('customer.bookings.partials.tabs', ['active' => 'upcoming'])

    <form method="GET" class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="relative flex-1 sm:max-w-sm">
            <x-icon name="search" class="pointer-events-none absolute start-4 top-1/2 h-4 w-4 -translate-y-1/2 text-luxury-muted" />
            <input type="search" name="search" value="{{ $search }}" placeholder="{{ __('Search by booking ID, pickup, or drop-off...') }}"
                class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal py-2.5 ps-11 pe-4 text-sm text-luxury-white placeholder:text-luxury-muted/70 focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
        </div>
        <button type="submit" class="tap-scale inline-flex items-center justify-center gap-2 rounded-lg bg-luxury-gold px-4 py-2.5 text-sm font-semibold text-luxury-black transition hover:bg-luxury-gold-light">
            {{ __('Search') }}
        </button>
        @if ($search)
            <a href="{{ route('customer.bookings.upcoming') }}" class="text-sm text-luxury-muted hover:text-luxury-white">{{ __('Clear') }}</a>
        @endif
    </form>

    <div class="mx-auto max-w-2xl space-y-5">
        @forelse ($bookings as $booking)
            <div class="overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal"
                x-data="countdownTimer('{{ $booking->pickup_datetime->toIso8601String() }}')" x-init="start()">
                {{-- Header: status + countdown --}}
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-luxury-border bg-luxury-graphite/40 px-5 py-3">
                    <div class="flex items-center gap-2">
                        <x-customer.status-badge :status="$booking->status" />
                        <span class="text-xs text-luxury-muted">{{ $booking->booking_number }}</span>
                    </div>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-luxury-gold/10 px-3 py-1 text-xs font-semibold text-luxury-gold" :class="{ 'animate-pulse': isSoon }">
                        <x-icon name="clock" class="h-3.5 w-3.5" />
                        <span x-text="label"></span>
                    </span>
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
                            <p class="truncate text-sm font-medium text-luxury-white">{{ $booking->pickup_location }}</p>
                            <p class="truncate text-sm font-medium text-luxury-white">{{ $booking->dropoff_location }}</p>
                        </div>
                        <p class="shrink-0 text-end text-xs text-luxury-muted">{{ $booking->pickup_datetime->format('M d, Y — h:i A') }}</p>
                    </div>

                    {{-- Driver + Vehicle --}}
                    <div class="mt-5 grid grid-cols-1 gap-3 border-t border-luxury-border pt-5 sm:grid-cols-2">
                        {{-- Driver --}}
                        <div class="flex items-center gap-3 rounded-xl bg-luxury-graphite/40 p-3">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-full border border-luxury-border bg-luxury-graphite">
                                @if ($booking->driver?->photo_url)
                                    <x-lazy-image :src="$booking->driver->photo_url" :alt="$booking->driver->name" />
                                @else
                                    <x-icon name="user" class="h-5 w-5 text-luxury-muted" />
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-xs font-medium text-luxury-white">{{ $booking->driver?->name ?? __('Not yet assigned') }}</p>
                                @if ($booking->driver?->average_rating)
                                    <p class="flex items-center gap-1 text-[11px] text-luxury-muted">
                                        <x-icon name="star" class="h-3 w-3 text-luxury-gold" /> {{ $booking->driver->average_rating }}
                                    </p>
                                @endif
                            </div>
                            @if ($booking->driver?->phone)
                                <a href="tel:{{ $booking->driver->phone }}" aria-label="{{ __('Call driver') }}"
                                    class="tap-scale flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-luxury-border text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                    <x-icon name="phone" class="h-3.5 w-3.5" />
                                </a>
                            @endif
                        </div>

                        {{-- Vehicle --}}
                        <div class="flex items-center gap-3 rounded-xl bg-luxury-graphite/40 p-3">
                            <div class="h-11 w-11 shrink-0 overflow-hidden rounded-lg border border-luxury-border bg-luxury-graphite">
                                @if ($booking->vehicle?->image_url)
                                    <x-lazy-image :src="$booking->vehicle->image_url" :alt="$booking->vehicle->name" />
                                @else
                                    <div class="flex h-full w-full items-center justify-center">
                                        <x-icon name="car" class="h-5 w-5 text-luxury-muted" />
                                    </div>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-xs font-medium text-luxury-white">{{ $booking->vehicle?->category?->name ?? $booking->vehicle?->name ?? '—' }}</p>
                                @if ($booking->vehicle?->plate_number)
                                    <p class="text-[11px] uppercase tracking-wide text-luxury-muted">{{ $booking->vehicle->plate_number }}</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Fare + Actions --}}
                    <div class="mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-luxury-border pt-5">
                        <div>
                            <p class="text-[11px] uppercase tracking-wide text-luxury-muted">{{ __('Fare') }}</p>
                            <p class="text-lg font-semibold text-luxury-gold">{{ currency($booking->fare_amount) }}</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('customer.support.create') }}"
                                class="tap-scale inline-flex items-center gap-1.5 rounded-lg border border-luxury-border px-3 py-2 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                <x-icon name="chat" class="h-3.5 w-3.5" />
                                {{ __('Support') }}
                            </a>

                            @if (in_array($booking->status, $cancellableStatuses, true))
                                <x-customer.cancel-booking-modal :booking="$booking" />
                            @endif

                            <a href="{{ route('customer.bookings.show', $booking) }}"
                                class="tap-scale inline-flex items-center gap-1.5 rounded-lg bg-luxury-gold px-4 py-2 text-xs font-semibold text-luxury-black transition hover:bg-luxury-gold-light">
                                {{ __('View Booking') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-12 text-center">
                <x-icon name="clock" class="mx-auto h-10 w-10 text-luxury-muted" />
                <p class="mt-4 text-sm text-luxury-muted">
                    {{ $search ? __('No upcoming trips match your search.') : __("You don't have any upcoming trips.") }}
                </p>
                @unless ($search)
                    <a href="{{ route('pages.home') }}" class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-luxury-gold hover:text-luxury-gold-light">
                        {{ __('Book a ride') }}
                        <x-icon name="chevron-right" class="h-4 w-4 rtl:rotate-180" />
                    </a>
                @endunless
            </div>
        @endforelse
    </div>

    @if ($bookings->hasPages())
        <div class="mx-auto mt-6 flex max-w-2xl items-center justify-between text-sm text-luxury-muted">
            <div>
                @if ($bookings->onFirstPage())
                    <span class="cursor-not-allowed rounded-lg border border-luxury-border px-3 py-1.5 opacity-40">{{ __('Previous') }}</span>
                @else
                    <a href="{{ $bookings->previousPageUrl() }}" class="rounded-lg border border-luxury-border px-3 py-1.5 hover:border-luxury-gold/40 hover:text-luxury-gold">{{ __('Previous') }}</a>
                @endif
            </div>
            <p>{{ __('Page :current of :last', ['current' => $bookings->currentPage(), 'last' => $bookings->lastPage()]) }}</p>
            <div>
                @if ($bookings->hasMorePages())
                    <a href="{{ $bookings->nextPageUrl() }}" class="rounded-lg border border-luxury-border px-3 py-1.5 hover:border-luxury-gold/40 hover:text-luxury-gold">{{ __('Next') }}</a>
                @else
                    <span class="cursor-not-allowed rounded-lg border border-luxury-border px-3 py-1.5 opacity-40">{{ __('Next') }}</span>
                @endif
            </div>
        </div>
    @endif

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
</x-customer.layouts.app>
