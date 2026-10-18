@props(['booking'])

{{--
    Shareable, no-login-required payment link for a booking — reuses the
    existing public booking.confirmation URL, since that page already shows
    the payment buttons for anyone holding the booking number (see
    booking-payment-buttons.blade.php). One link works for the admin sharing
    it with a customer who hasn't paid, and for a customer forwarding it to
    a friend or parent to pay on their behalf.

    Styled with the electric-blue "technology/secure link" accent (see the
    payment card's own security row for the same accent) so it reads as a
    distinct secondary action next to the gold "pay now" buttons, in both
    the always-dark public confirmation page and the admin panel's own
    light/dark theme.
--}}
@php
    $paymentLink = route('booking.confirmation', $booking->booking_number);
@endphp

<div x-data="{ copied: false }" {{ $attributes->merge(['class' => 'inline-flex']) }}>
    <button type="button"
        @click="navigator.clipboard.writeText(@js($paymentLink)).then(() => { copied = true; setTimeout(() => (copied = false), 2200); })"
        class="group inline-flex items-center gap-2 rounded-lg border border-blue-400/30 bg-luxury-graphite px-4 py-2.5 text-sm font-medium text-luxury-white transition
               hover:border-blue-400/60 hover:bg-luxury-slate active:scale-[0.98]
               focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-400/50">
        <x-icon name="link" class="h-4 w-4 text-blue-300 transition group-hover:text-blue-200" x-show="!copied" />
        <x-icon name="check-circle" class="h-4 w-4 text-emerald-400" x-show="copied" x-cloak />
        <span x-text="copied ? '{{ __('Payment Link Copied') }}' : '{{ __('Copy Payment Link') }}'"></span>
    </button>
</div>
