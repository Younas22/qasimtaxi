@props(['booking'])

@php
    $gateways = \App\Models\PaymentGateway::whereIn('code', ['stripe', 'paypal'])->get()->keyBy('code');
    $stripeReady = $gateways->get('stripe')?->isReady() ?? false;
    $paypalReady = $gateways->get('paypal')?->isReady() ?? false;
    $isPayable = $booking->payment_status !== 'paid' && $booking->status !== 'cancelled';
    // An online gateway being active means this booking's confirmation is
    // gated on payment (see BookingCreationService::applyPolicies()) —
    // reflect that in the copy rather than the generic "complete your
    // payment" line once it's still sitting in "pending".
    $awaitingConfirmation = $isPayable && $booking->status === 'pending' && ($stripeReady || $paypalReady);
    // Only spread the buttons into a 2-up row once there are actually two
    // to show — with a single active gateway, forcing sm:grid-cols-2 would
    // leave it stuck at half width next to an empty column.
    $activeGatewayCount = ($stripeReady ? 1 : 0) + ($paypalReady ? 1 : 0);
@endphp

@if ($isPayable && ($stripeReady || $paypalReady))
    <div {{ $attributes->merge(['class' => 'relative overflow-hidden rounded-2xl border border-luxury-border bg-luxury-graphite/70 p-6 text-left sm:p-7']) }}>
        {{-- Ambient gold/blue wash — purely decorative, sits behind everything. --}}
        <div class="pointer-events-none absolute -right-12 -top-16 h-48 w-48 rounded-full bg-luxury-gold/10 blur-3xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -bottom-16 -left-10 h-40 w-40 rounded-full bg-blue-500/10 blur-3xl" aria-hidden="true"></div>

        <div class="relative">
            {{-- Label + hairline divider — the label wraps to its own line
                 on narrow screens rather than forcing single-line overflow
                 (the "Payment Required to Confirm Booking" copy is too long
                 to share a row with the divider below ~360px). --}}
            <div class="flex flex-wrap items-center gap-x-3 gap-y-1.5">
                <p class="text-[11px] font-semibold uppercase leading-snug tracking-[0.18em] text-luxury-gold">
                    {{ $awaitingConfirmation ? __('Payment Required to Confirm Booking') : __('Complete Your Payment') }}
                </p>
                <span class="h-px min-w-6 flex-1 bg-gradient-to-r from-luxury-gold/40 via-luxury-border to-transparent"></span>
            </div>

            {{-- Dominant amount --}}
            <div class="relative mt-3 inline-block">
                <p class="text-4xl font-bold tracking-tight text-luxury-white sm:text-[2.75rem]">{{ currency($booking->fare_amount) }}</p>
                <span class="pointer-events-none absolute -bottom-1 left-1 h-3 w-4/5 bg-gradient-to-r from-luxury-gold/25 via-blue-500/10 to-transparent blur-lg" aria-hidden="true"></span>
            </div>

            {{-- Payment method cards — real links, styled as premium selectable controls --}}
            <div class="mt-6 grid grid-cols-1 gap-3 {{ $activeGatewayCount > 1 ? 'sm:grid-cols-2' : '' }}">
                @if ($stripeReady)
                    <a href="{{ route('booking.pay', [$booking->booking_number, 'stripe']) }}"
                        class="group relative flex min-h-[3.25rem] items-center gap-2.5 rounded-xl border border-luxury-border bg-luxury-charcoal px-3 py-3.5 transition
                               hover:border-luxury-gold/50 hover:bg-luxury-slate
                               focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-luxury-gold/50
                               active:scale-[0.98]">
                        <span class="flex h-10 w-11 shrink-0 items-center justify-center">
                            <img src="{{ asset('images/payments/card-mark.png') }}" alt="" class="h-full w-full object-contain drop-shadow-[0_4px_10px_rgba(201,162,75,0.25)]">
                        </span>
                        <span class="min-w-0 text-left">
                            <span class="block whitespace-nowrap text-[11px] font-medium uppercase tracking-wide text-luxury-muted">{{ __('Pay with') }}</span>
                            <span class="block text-sm font-semibold text-luxury-white">{{ __('Card') }}</span>
                        </span>
                        <span class="pointer-events-none absolute right-3 top-3 flex h-4 w-4 items-center justify-center rounded-full bg-luxury-gold text-luxury-black opacity-0 transition group-hover:opacity-100 group-focus-visible:opacity-100" aria-hidden="true">
                            <svg viewBox="0 0 12 12" class="h-2.5 w-2.5"><path d="M2.5 6.5l2.2 2.2L9.5 3.5" stroke="currentColor" stroke-width="1.6" fill="none" stroke-linecap="round" stroke-linejoin="round" /></svg>
                        </span>
                    </a>
                @endif
                @if ($paypalReady)
                    <a href="{{ route('booking.pay', [$booking->booking_number, 'paypal']) }}"
                        class="group relative flex min-h-[3.25rem] items-center gap-2.5 rounded-xl border border-luxury-border bg-luxury-charcoal px-3 py-3.5 transition
                               hover:border-blue-400/40 hover:bg-luxury-slate
                               focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-400/50
                               active:scale-[0.98]">
                        <span class="flex h-10 w-11 shrink-0 items-center justify-center rounded-lg border border-luxury-border bg-white transition group-hover:border-blue-400/30 group-focus-visible:border-blue-400/30">
                            <img src="{{ asset('images/payments/paypal-mark.svg') }}" alt="PayPal" class="h-6 w-6 object-contain">
                        </span>
                        <span class="min-w-0 text-left">
                            <span class="block whitespace-nowrap text-[11px] font-medium uppercase tracking-wide text-luxury-muted">{{ __('Pay with') }}</span>
                            <span class="block text-sm font-semibold text-luxury-white">PayPal</span>
                        </span>
                        <span class="pointer-events-none absolute right-3 top-3 flex h-4 w-4 items-center justify-center rounded-full bg-blue-400 text-luxury-black opacity-0 transition group-hover:opacity-100 group-focus-visible:opacity-100" aria-hidden="true">
                            <svg viewBox="0 0 12 12" class="h-2.5 w-2.5"><path d="M2.5 6.5l2.2 2.2L9.5 3.5" stroke="currentColor" stroke-width="1.6" fill="none" stroke-linecap="round" stroke-linejoin="round" /></svg>
                        </span>
                    </a>
                @endif
            </div>

            {{-- Security reassurance --}}
            <div class="mt-5 flex items-start gap-2.5">
                <x-icon name="lock" class="mt-0.5 h-4 w-4 shrink-0 text-luxury-gold/70" />
                <p class="text-xs leading-snug text-luxury-muted">
                    @if ($awaitingConfirmation)
                        {{ __("You'll be redirected to a secure payment page. This booking is confirmed only once payment is received.") }}
                    @else
                        {{ __("You'll be redirected to a secure payment page. Your booking stays reserved either way.") }}
                    @endif
                </p>
            </div>

            {{-- Someone-else-is-paying: shareable link --}}
            <div class="mt-5 flex items-start gap-3 rounded-xl border border-luxury-border/70 bg-luxury-charcoal/70 p-4">
                <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-blue-400/25 bg-blue-400/10 text-blue-300">
                    <x-icon name="link" class="h-4 w-4" />
                </span>
                <div class="min-w-0 flex-1">
                    <p class="text-xs leading-snug text-luxury-muted">
                        {{ __("Paying for someone else? Copy this link and send it to whoever's covering the fare — no account needed.") }}
                    </p>
                    <x-copy-payment-link-button :booking="$booking" class="mt-3 inline-flex" />
                </div>
            </div>
        </div>
    </div>
@endif
