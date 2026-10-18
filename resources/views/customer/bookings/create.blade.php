<x-customer.layouts.app :title="__('Book a Ride')">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Book a Ride') }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ __('Set your pickup, drop-off, and trip details — the same booking engine as our homepage, right here in your account.') }}</p>
    </div>

    {{--
        Reuses the exact same public booking widget component the homepage
        uses — same Alpine state, same fare-quote AJAX, same submit route.
        It already recognises a logged-in customer guard on submit, so the
        booking is created and linked to this account with zero duplicated
        booking logic.
    --}}
    <div class="mx-auto max-w-2xl">
        <x-booking-search-box />
    </div>
</x-customer.layouts.app>
