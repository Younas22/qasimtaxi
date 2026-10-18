<x-customer.layouts.app :title="__('Completed Trips')">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Completed Trips') }}</h2>
            <p class="mt-1 text-sm text-luxury-muted">{{ __('Your ride history, drivers, and vehicles.') }}</p>
        </div>
        <div class="rounded-2xl border border-luxury-gold/30 bg-gradient-to-br from-luxury-charcoal to-luxury-graphite px-5 py-3 sm:text-end">
            <p class="text-[11px] font-medium uppercase tracking-wider text-luxury-muted">{{ __('Total Amount Paid') }}</p>
            <p class="text-2xl font-semibold text-luxury-gold">{{ currency($totalPaid) }}</p>
        </div>
    </div>

    @include('customer.bookings.partials.tabs', ['active' => 'completed'])

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
            <a href="{{ route('customer.bookings.completed') }}" class="text-sm text-luxury-muted hover:text-luxury-white">{{ __('Clear') }}</a>
        @endif
    </form>

    <div class="mx-auto max-w-2xl space-y-5">
        @forelse ($bookings as $booking)
            <div class="overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-luxury-border bg-luxury-graphite/40 px-5 py-3">
                    <div class="flex items-center gap-2">
                        <x-customer.status-badge :status="$booking->status" />
                        <span class="text-xs text-luxury-muted">{{ $booking->booking_number }}</span>
                    </div>
                    <span class="text-xs text-luxury-muted">{{ $booking->pickup_datetime->format('M d, Y') }}</span>
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
                    </div>

                    {{-- Driver + Vehicle --}}
                    <div class="mt-5 grid grid-cols-1 gap-3 border-t border-luxury-border pt-5 sm:grid-cols-2">
                        <div class="flex items-center gap-3 rounded-xl bg-luxury-graphite/40 p-3">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-full border border-luxury-border bg-luxury-graphite">
                                @if ($booking->driver?->photo_url)
                                    <x-lazy-image :src="$booking->driver->photo_url" :alt="$booking->driver->name" />
                                @else
                                    <x-icon name="user" class="h-5 w-5 text-luxury-muted" />
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-xs font-medium text-luxury-white">{{ $booking->driver?->name ?? '—' }}</p>
                                @if ($booking->driver?->average_rating)
                                    <p class="flex items-center gap-1 text-[11px] text-luxury-muted">
                                        <x-icon name="star" class="h-3 w-3 text-luxury-gold" /> {{ $booking->driver->average_rating }}
                                    </p>
                                @endif
                            </div>
                        </div>

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
                            <p class="text-[11px] uppercase tracking-wide text-luxury-muted">{{ __('Amount Paid') }}</p>
                            <p class="text-lg font-semibold text-luxury-gold">{{ currency($booking->fare_amount) }}</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('booking.invoice', $booking->booking_number) }}" target="_blank"
                                class="tap-scale inline-flex items-center gap-1.5 rounded-lg border border-luxury-border px-3 py-2 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                <x-icon name="download" class="h-3.5 w-3.5" />
                                {{ __('Invoice') }}
                            </a>

                            <a href="{{ route('pages.home', ['pickup' => $booking->pickup_location, 'dropoff' => $booking->dropoff_location, 'vehicle_category' => $booking->vehicle?->vehicle_category_id]).'#booking-widget' }}"
                                class="tap-scale inline-flex items-center gap-1.5 rounded-lg border border-luxury-border px-3 py-2 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                <x-icon name="car" class="h-3.5 w-3.5" />
                                {{ __('Rebook') }}
                            </a>

                            @if ($booking->review)
                                <span class="inline-flex items-center gap-1.5 rounded-lg bg-luxury-gold/10 px-3 py-2 text-xs font-medium text-luxury-gold">
                                    <x-icon name="star" class="h-3.5 w-3.5" />
                                    {{ __('Reviewed') }}
                                </span>
                            @else
                                <a href="{{ route('customer.reviews.create', $booking) }}"
                                    class="tap-scale inline-flex items-center gap-1.5 rounded-lg bg-luxury-gold px-3 py-2 text-xs font-semibold text-luxury-black transition hover:bg-luxury-gold-light">
                                    <x-icon name="star" class="h-3.5 w-3.5" />
                                    {{ __('Leave Review') }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-12 text-center">
                <x-icon name="check-circle" class="mx-auto h-10 w-10 text-luxury-muted" />
                <p class="mt-4 text-sm text-luxury-muted">
                    {{ $search ? __('No completed trips match your search.') : __("You don't have any completed trips yet.") }}
                </p>
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
</x-customer.layouts.app>
