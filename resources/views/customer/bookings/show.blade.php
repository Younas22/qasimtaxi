<x-customer.layouts.app :title="__('Booking').' '.$booking->booking_number">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-luxury-white">{{ $booking->booking_number }}</h2>
            <p class="mt-1 text-sm text-luxury-muted">{{ __('Booked on :date', ['date' => $booking->created_at->format('M d, Y')]) }}</p>
        </div>
        <a href="{{ route('customer.bookings.index') }}" class="text-sm text-luxury-muted hover:text-luxury-white">&larr; {{ __('Back') }}</a>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-4 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6 lg:col-span-2">
            <div class="flex items-center justify-between">
                <x-customer.status-badge :status="$booking->status" class="text-sm" />
                <a href="{{ route('booking.invoice', $booking->booking_number) }}" target="_blank"
                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-luxury-gold hover:text-luxury-gold-light">
                    <x-icon name="download" class="h-3.5 w-3.5" />
                    {{ __('Download Invoice') }}
                </a>
            </div>

            <div class="grid grid-cols-1 gap-4 border-t border-luxury-border pt-4 sm:grid-cols-2">
                <div>
                    <p class="text-xs uppercase tracking-wide text-luxury-muted">{{ __('Pickup') }}</p>
                    <p class="mt-1 text-sm font-medium text-luxury-white">{{ $booking->pickup_location }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-luxury-muted">{{ __('Drop-off') }}</p>
                    <p class="mt-1 text-sm font-medium text-luxury-white">{{ $booking->dropoff_location }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-luxury-muted">{{ __('Date & Time') }}</p>
                    <p class="mt-1 text-sm font-medium text-luxury-white">{{ $booking->pickup_datetime->format('M d, Y — h:i A') }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-luxury-muted">{{ __('Vehicle') }}</p>
                    <p class="mt-1 text-sm font-medium text-luxury-white">{{ $booking->vehicle?->category?->name ?? $booking->vehicle?->name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-luxury-muted">{{ __('Passengers') }}</p>
                    <p class="mt-1 text-sm font-medium text-luxury-white">{{ $booking->passengers }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-luxury-muted">{{ __('Luggage') }}</p>
                    <p class="mt-1 text-sm font-medium text-luxury-white">{{ $booking->luggage }}</p>
                </div>
            </div>

            @if ($booking->driver && $booking->status === 'in_progress')
                <div class="border-t border-luxury-border pt-4">
                    <p class="text-xs uppercase tracking-wide text-luxury-muted">{{ __('Your Ride') }}</p>
                    <p class="mt-1 text-sm font-medium text-luxury-white">
                        {{ __('Your ride is in progress with :name.', ['name' => $booking->driver->name]) }}
                    </p>
                    @if ($booking->estimated_arrival_at)
                        <p class="mt-1 text-xs text-luxury-muted">
                            {{ __('Estimated arrival') }}: {{ $booking->estimated_arrival_at->format('h:i A') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="space-y-4">
            @if ($dispatch)
                <x-dispatch-card :booking="$booking" :dispatch="$dispatch" :poll-url="route('customer.bookings.dispatch', $booking)" />
            @endif

            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
                <p class="text-xs uppercase tracking-wide text-luxury-muted">{{ __('Payment Status') }}</p>
                <div class="mt-2"><x-customer.payment-badge :status="$booking->payment_status" /></div>
                <div class="mt-4 border-t border-luxury-border pt-4">
                    <p class="text-xs uppercase tracking-wide text-luxury-muted">{{ __('Total Fare') }}</p>
                    <p class="mt-1 text-2xl font-semibold text-luxury-gold">{{ currency($booking->fare_amount) }}</p>
                </div>
            </div>

            <x-booking-payment-buttons :booking="$booking" />

            @if ($booking->status === 'completed')
                @if ($booking->review)
                    <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6 text-center">
                        <x-icon name="check-circle" class="mx-auto h-6 w-6 text-emerald-400" />
                        <p class="mt-2 text-sm font-medium text-luxury-white">{{ __('Review Submitted') }}</p>
                        <div class="mt-2 flex justify-center"><x-rating-stars :rating="$booking->review->rating" size="h-3.5 w-3.5" /></div>
                    </div>
                @else
                    <a href="{{ route('customer.reviews.create', $booking) }}" class="block rounded-2xl border border-luxury-border bg-luxury-charcoal p-6 text-center transition hover:border-luxury-gold/40">
                        <x-icon name="star" class="mx-auto h-6 w-6 text-luxury-gold" />
                        <p class="mt-2 text-sm font-medium text-luxury-white">{{ __('Leave a Review') }}</p>
                    </a>
                @endif

                <a href="{{ route('pages.home', ['pickup' => $booking->pickup_location, 'dropoff' => $booking->dropoff_location, 'vehicle_category' => $booking->vehicle?->vehicle_category_id]).'#booking-widget' }}"
                    class="block rounded-2xl border border-luxury-border bg-luxury-charcoal p-6 text-center transition hover:border-luxury-gold/40">
                    <x-icon name="car" class="mx-auto h-6 w-6 text-luxury-gold" />
                    <p class="mt-2 text-sm font-medium text-luxury-white">{{ __('Rebook This Ride') }}</p>
                </a>
            @endif

            @if (in_array($booking->status, ['pending', 'confirmed', 'assigned'], true))
                <div class="space-y-3 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
                    <a href="{{ route('customer.support.create') }}"
                        class="tap-scale flex w-full items-center justify-center gap-2 rounded-lg border border-luxury-border px-4 py-2.5 text-sm font-medium text-luxury-white transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                        <x-icon name="chat" class="h-4 w-4" />
                        {{ __('Contact Support') }}
                    </a>
                    <x-customer.cancel-booking-modal :booking="$booking"
                        button-class="tap-scale flex w-full items-center justify-center gap-2 rounded-lg border border-red-500/30 px-4 py-2.5 text-sm font-medium text-red-400 transition hover:bg-red-500/10"
                        button-label="{{ __('Cancel Booking') }}" />
                </div>
            @endif
        </div>
    </div>
</x-customer.layouts.app>
