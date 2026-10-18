<x-customer.layouts.app :title="__('Leave a Review')">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Leave a Review') }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ __('Tell us about your trip on :date.', ['date' => $booking->pickup_datetime->format('M d, Y')]) }}</p>
    </div>

    <div class="max-w-xl rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
        <div class="mb-5 rounded-xl bg-luxury-graphite/40 p-4 text-sm text-luxury-muted">
            <p><span class="text-luxury-white">{{ $booking->booking_number }}</span> &middot; {{ $booking->pickup_location }} &rarr; {{ $booking->dropoff_location }}</p>
            @if ($booking->driver || $booking->vehicle)
                <p class="mt-1">
                    @if ($booking->driver){{ __('Driver') }}: {{ $booking->driver->name }}@endif
                    @if ($booking->driver && $booking->vehicle) &middot; @endif
                    @if ($booking->vehicle){{ __('Vehicle') }}: {{ $booking->vehicle->category?->name ?? $booking->vehicle->name }}@endif
                </p>
            @endif
        </div>

        <form method="POST" action="{{ route('customer.reviews.store', $booking) }}" class="space-y-5">
            @csrf

            <div>
                <x-admin.input-label value="Your Rating" />
                <x-admin.rating-input name="rating" :value="old('rating', 5)" />
                <x-admin.input-error :messages="$errors->get('rating')" />
            </div>

            <div>
                <x-admin.input-label for="comment" value="Your Review (optional)" />
                <textarea id="comment" name="comment" rows="5" placeholder="{{ __('How was your ride?') }}"
                    class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">{{ old('comment') }}</textarea>
                <x-admin.input-error :messages="$errors->get('comment')" />
            </div>

            <div class="flex items-center gap-3">
                <x-admin.button type="submit" variant="primary">{{ __('Submit Review') }}</x-admin.button>
                <a href="{{ route('customer.bookings.show', $booking) }}" class="text-sm text-luxury-muted hover:text-luxury-white">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
</x-customer.layouts.app>
