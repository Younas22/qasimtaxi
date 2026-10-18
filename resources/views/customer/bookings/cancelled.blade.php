<x-customer.layouts.app :title="__('Cancelled Trips')">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Cancelled Trips') }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ __('Cancellation reasons and refund status for your cancelled rides.') }}</p>
    </div>

    @include('customer.bookings.partials.tabs', ['active' => 'cancelled'])

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
            <a href="{{ route('customer.bookings.cancelled') }}" class="text-sm text-luxury-muted hover:text-luxury-white">{{ __('Clear') }}</a>
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
                    <span class="text-xs text-luxury-muted">{{ __('Cancelled on :date', ['date' => $booking->updated_at->format('M d, Y')]) }}</span>
                </div>

                <div class="p-5">
                    {{-- Route --}}
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5 flex flex-col items-center">
                            <span class="h-2 w-2 shrink-0 rounded-full bg-luxury-border"></span>
                            <span class="my-1 h-8 w-px bg-luxury-border"></span>
                            <x-icon name="map-pin" class="h-3.5 w-3.5 shrink-0 text-luxury-muted" />
                        </div>
                        <div class="min-w-0 flex-1 space-y-3">
                            <p class="truncate text-sm font-medium text-luxury-white">{{ $booking->pickup_location }}</p>
                            <p class="truncate text-sm font-medium text-luxury-white">{{ $booking->dropoff_location }}</p>
                        </div>
                        <p class="shrink-0 text-end text-xs text-luxury-muted">{{ $booking->pickup_datetime->format('M d, Y — h:i A') }}</p>
                    </div>

                    {{-- Cancellation reason + Refund status --}}
                    <div class="mt-5 grid grid-cols-1 gap-3 border-t border-luxury-border pt-5 sm:grid-cols-2">
                        <div class="rounded-xl bg-luxury-graphite/40 p-3">
                            <p class="text-[11px] uppercase tracking-wide text-luxury-muted">{{ __('Cancellation Reason') }}</p>
                            <p class="mt-1 text-sm font-medium text-luxury-white">{{ $booking->cancellation_reason_label ?? __('Not specified') }}</p>
                        </div>
                        <div class="rounded-xl bg-luxury-graphite/40 p-3">
                            <p class="text-[11px] uppercase tracking-wide text-luxury-muted">{{ __('Refund Status') }}</p>
                            <div class="mt-1"><x-customer.refund-badge :status="$booking->refund_status" /></div>
                        </div>
                    </div>

                    {{-- Booking details --}}
                    <div class="mt-5 grid grid-cols-1 gap-3 border-t border-luxury-border pt-5 text-xs sm:grid-cols-2 md:grid-cols-4">
                        <div>
                            <p class="text-luxury-muted">{{ __('Vehicle') }}</p>
                            <p class="mt-0.5 font-medium text-luxury-white">{{ $booking->vehicle?->category?->name ?? $booking->vehicle?->name ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-luxury-muted">{{ __('Driver') }}</p>
                            <p class="mt-0.5 font-medium text-luxury-white">{{ $booking->driver?->name ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-luxury-muted">{{ __('Fare') }}</p>
                            <p class="mt-0.5 font-medium text-luxury-white">{{ currency($booking->fare_amount) }}</p>
                        </div>
                        <div>
                            <p class="text-luxury-muted">{{ __('Payment') }}</p>
                            <p class="mt-0.5"><x-customer.payment-badge :status="$booking->payment_status" /></p>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="mt-5 flex flex-wrap items-center gap-2 border-t border-luxury-border pt-5">
                        <a href="{{ route('pages.home', ['pickup' => $booking->pickup_location, 'dropoff' => $booking->dropoff_location, 'vehicle_category' => $booking->vehicle?->vehicle_category_id]).'#booking-widget' }}"
                            class="tap-scale inline-flex items-center gap-1.5 rounded-lg bg-luxury-gold px-4 py-2 text-xs font-semibold text-luxury-black transition hover:bg-luxury-gold-light">
                            <x-icon name="car" class="h-3.5 w-3.5" />
                            {{ __('Rebook Ride') }}
                        </a>
                        <a href="{{ route('customer.support.create') }}"
                            class="tap-scale inline-flex items-center gap-1.5 rounded-lg border border-luxury-border px-3 py-2 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                            <x-icon name="chat" class="h-3.5 w-3.5" />
                            {{ __('Contact Support') }}
                        </a>
                        <a href="{{ route('customer.bookings.show', $booking) }}"
                            class="ms-auto text-xs font-medium text-luxury-muted hover:text-luxury-white">
                            {{ __('View Details') }}
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-12 text-center">
                <x-icon name="close" class="mx-auto h-10 w-10 text-luxury-muted" />
                <p class="mt-4 text-sm text-luxury-muted">
                    {{ $search ? __('No cancelled trips match your search.') : __("You don't have any cancelled trips.") }}
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
