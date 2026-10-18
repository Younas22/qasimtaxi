@php
    $navigateTarget = $booking->status === 'in_progress' ? $booking->dropoff_location : $booking->pickup_location;
    $navigateUrl = 'https://www.google.com/maps/dir/?api=1&destination=' . urlencode($navigateTarget);
    $hasPrimaryAction = in_array($booking->status, ['assigned', 'in_progress'], true);
@endphp

<x-driver.layouts.app :title="$booking->booking_number">
    {{-- Extra bottom padding on mobile only while a sticky action bar is
         present, so it never covers the last card. --}}
    <div class="{{ $hasPrimaryAction ? 'pb-24 lg:pb-0' : '' }}">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-luxury-white">{{ $booking->booking_number }}</h2>
                <p class="mt-1 text-sm text-luxury-muted">{{ __('Booked on :date', ['date' => $booking->created_at->format('M d, Y')]) }}</p>
            </div>
            <a href="{{ route('driver.bookings.index') }}" class="hidden text-sm text-luxury-muted hover:text-luxury-white lg:block">&larr; {{ __('Back') }}</a>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6 lg:col-span-2">
                <div class="flex items-center justify-between">
                    <x-customer.status-badge :status="$booking->status" class="!px-3 !py-1.5 !text-sm" />

                    {{-- Desktop-only inline action — mobile uses the sticky bar below. --}}
                    <div class="hidden lg:block">
                        @if ($booking->status === 'assigned')
                            <form method="POST" action="{{ route('driver.bookings.start', $booking) }}">
                                @csrf
                                <x-admin.button type="submit" variant="primary">{{ __('Start Ride') }}</x-admin.button>
                            </form>
                        @elseif ($booking->status === 'in_progress')
                            <form method="POST" action="{{ route('driver.bookings.complete', $booking) }}">
                                @csrf
                                <x-admin.button type="submit" variant="primary">{{ __('Complete Ride') }}</x-admin.button>
                            </form>
                        @endif
                    </div>
                </div>

                {{-- Route --}}
                <div class="flex items-start gap-3 border-t border-luxury-border pt-5">
                    <div class="mt-0.5 flex flex-col items-center">
                        <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-luxury-gold"></span>
                        <span class="my-1 h-10 w-px bg-luxury-border"></span>
                        <x-icon name="map-pin" class="h-4 w-4 shrink-0 text-luxury-gold" />
                    </div>
                    <div class="min-w-0 flex-1 space-y-4">
                        <div>
                            <p class="text-[11px] uppercase tracking-wide text-luxury-muted">{{ __('Pickup') }}</p>
                            <p class="mt-0.5 text-sm font-medium text-luxury-white">{{ $booking->pickup_location }}</p>
                        </div>
                        <div>
                            <p class="text-[11px] uppercase tracking-wide text-luxury-muted">{{ __('Drop-off') }}</p>
                            <p class="mt-0.5 text-sm font-medium text-luxury-white">{{ $booking->dropoff_location }}</p>
                        </div>
                    </div>
                </div>

                @if (in_array($booking->status, ['assigned', 'in_progress'], true))
                    <a href="{{ $navigateUrl }}" target="_blank" rel="noopener"
                        class="tap-scale flex items-center justify-center gap-2 rounded-lg border border-luxury-border px-4 py-3 text-sm font-semibold text-luxury-white transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                        <x-icon name="map-pin" class="h-4 w-4" />
                        {{ $booking->status === 'in_progress' ? __('Navigate to Drop-off') : __('Navigate to Pickup') }}
                    </a>
                @endif

                <div class="grid grid-cols-2 gap-4 border-t border-luxury-border pt-5 sm:grid-cols-3">
                    <div>
                        <p class="text-[11px] uppercase tracking-wide text-luxury-muted">{{ __('Date & Time') }}</p>
                        <p class="mt-1 text-sm font-medium text-luxury-white">{{ $booking->pickup_datetime->format('M d, Y — h:i A') }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] uppercase tracking-wide text-luxury-muted">{{ __('Vehicle') }}</p>
                        <p class="mt-1 text-sm font-medium text-luxury-white">{{ $booking->vehicle?->category?->name ?? $booking->vehicle?->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] uppercase tracking-wide text-luxury-muted">{{ __('Passengers') }}</p>
                        <p class="mt-1 text-sm font-medium text-luxury-white">{{ $booking->passengers }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] uppercase tracking-wide text-luxury-muted">{{ __('Luggage') }}</p>
                        <p class="mt-1 text-sm font-medium text-luxury-white">{{ $booking->luggage }}</p>
                    </div>
                </div>

                @if ($booking->notes)
                    <div class="border-t border-luxury-border pt-5">
                        <p class="text-[11px] uppercase tracking-wide text-luxury-muted">{{ __('Notes') }}</p>
                        <p class="mt-1 text-sm text-luxury-white">{{ $booking->notes }}</p>
                    </div>
                @endif
            </div>

            <div class="space-y-4">
                <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
                    <div class="flex items-center gap-3">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-luxury-gold/10 text-luxury-gold">
                            <x-icon name="user" class="h-5 w-5" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-[11px] uppercase tracking-wide text-luxury-muted">{{ __('Passenger') }}</p>
                            <p class="truncate text-sm font-medium text-luxury-white">{{ $booking->customer?->name ?? '—' }}</p>
                        </div>
                    </div>
                    @if ($booking->customer?->phone)
                        <a href="tel:{{ $booking->customer->phone }}" class="tap-scale mt-4 flex items-center justify-center gap-2 rounded-lg border border-luxury-border px-4 py-3 text-sm font-semibold text-luxury-white transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                            <x-icon name="phone" class="h-4 w-4" />
                            {{ __('Call Passenger') }}
                        </a>
                    @endif
                </div>

                <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
                    <p class="text-[11px] uppercase tracking-wide text-luxury-muted">{{ __('Fare') }}</p>
                    <p class="mt-1 text-2xl font-semibold text-luxury-gold">{{ currency($booking->fare_amount) }}</p>
                    <div class="mt-2"><x-customer.payment-badge :status="$booking->payment_status" /></div>
                </div>

                @if ($booking->review)
                    <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6 text-center">
                        <p class="text-[11px] uppercase tracking-wide text-luxury-muted">{{ __('Customer Review') }}</p>
                        <div class="mt-2 flex justify-center"><x-rating-stars :rating="$booking->review->rating" size="h-3.5 w-3.5" /></div>
                        @if ($booking->review->comment)
                            <p class="mt-2 text-sm text-luxury-muted">{{ $booking->review->comment }}</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Sticky primary action — mobile only. One large, unmistakable,
         contextual button that follows the driver down the page instead of
         requiring a scroll back up to act on the ride. --}}
    @if ($hasPrimaryAction)
        <div class="pb-safe fixed inset-x-0 bottom-16 z-20 border-t border-luxury-border bg-luxury-charcoal/95 p-4 backdrop-blur lg:hidden">
            @if ($booking->status === 'assigned')
                <x-driver.ride-action-button :action="route('driver.bookings.start', $booking)" :label="__('Start Trip')" :loading-label="__('Starting…')" size="lg" />
            @else
                <x-driver.ride-action-button :action="route('driver.bookings.complete', $booking)" :label="__('Complete Trip')" :loading-label="__('Completing…')" size="lg" />
            @endif
        </div>
    @endif
</x-driver.layouts.app>
