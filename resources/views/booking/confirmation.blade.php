@php
    $phone = setting('phone');
    $whatsapp = setting('whatsapp');
    $whatsappDigits = $whatsapp ? preg_replace('/\D+/', '', $whatsapp) : null;
    $whatsappMessage = __('Hi! I\'d like some help with my booking :number.', ['number' => $booking->booking_number]);

    $gatewaysReady = \App\Models\PaymentGateway::whereIn('code', ['stripe', 'paypal'])->get()->contains(fn ($g) => $g->isReady());
    $awaitingPayment = $booking->status === 'pending' && $booking->payment_status !== 'paid' && $gatewaysReady;
@endphp

<x-layouts.public :title="'Booking Received'" :robots-index="false" :robots-follow="false">
    {{--
        Always-dark, regardless of the site's own light/dark toggle — a
        payment/confirmation screen reads as a checkout page, not a
        marketing page, and stays on-brand the same way hero.blade.php and
        pages/sections/cta.blade.php do (see .theme-dark-scope in app.css).
    --}}
    <div class="theme-dark-scope relative isolate overflow-hidden bg-luxury-black py-16 sm:py-24">
        {{-- Ambient atmosphere only — never allowed to compete with the UI. --}}
        <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
            <div class="absolute left-1/2 top-0 h-96 w-96 -translate-x-1/2 -translate-y-1/3 rounded-full bg-luxury-gold/10 blur-3xl"></div>
            <div class="absolute -bottom-24 -right-20 h-80 w-80 rounded-full bg-blue-500/10 blur-3xl"></div>
            <div class="absolute -bottom-28 -left-20 h-72 w-72 rounded-full bg-luxury-gold/5 blur-3xl"></div>
        </div>

        <div class="mx-auto max-w-lg px-4 sm:px-6 lg:px-8">
            <div class="relative overflow-hidden rounded-[2rem] border border-luxury-border bg-luxury-charcoal p-8 shadow-[0_30px_90px_-25px_rgba(0,0,0,0.75)] sm:p-11">
                {{-- Hairline inner highlight along the top edge — the classic premium-card detail. --}}
                <div class="pointer-events-none absolute inset-x-10 top-0 h-px bg-gradient-to-r from-transparent via-white/15 to-transparent" aria-hidden="true"></div>

                <div class="relative text-center">
                    {{-- Success indicator: glow halo + dual gold/blue ring + minimal check --}}
                    <div class="animate-fade-up relative mx-auto flex h-20 w-20 items-center justify-center">
                        <span class="absolute inset-0 rounded-full bg-luxury-gold/15 blur-xl" aria-hidden="true"></span>
                        <span class="absolute inset-0 rounded-full border border-luxury-gold/30"></span>
                        <span class="absolute inset-[3px] rounded-full border border-blue-400/20"></span>
                        <span class="relative flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-b from-luxury-gold/20 to-luxury-gold/5 text-luxury-gold ring-1 ring-luxury-gold/30">
                            <x-icon name="check-circle" class="h-7 w-7" />
                        </span>
                    </div>

                    <h1 class="animate-fade-up delay-1 relative mt-7 text-[1.75rem] font-bold leading-tight tracking-tight text-luxury-white sm:text-3xl">
                        {{ __('Booking Request') }} <span class="text-blue-400">{{ __('Received') }}</span>
                    </h1>

                    <p class="animate-fade-up delay-1 relative mx-auto mt-3 max-w-sm text-sm leading-relaxed text-luxury-muted">
                        {{ __('Thank you! Your reference number is') }}
                        <span class="mx-1 inline-flex items-center rounded-md border border-luxury-gold/30 bg-luxury-gold/10 px-2 py-0.5 align-middle text-xs font-semibold tracking-wide text-luxury-gold">{{ $booking->booking_number }}</span>.
                        @if ($awaitingPayment)
                            {{ __('Please complete payment below to confirm your ride.') }}
                        @else
                            {{ __('Our team will confirm your ride shortly.') }}
                        @endif
                    </p>
                </div>

                <x-booking-payment-buttons :booking="$booking" class="animate-fade-up delay-2 relative mt-8" />

                @if ($phone || $whatsappDigits)
                    <div class="animate-fade-up delay-3 relative mt-6 rounded-2xl border border-luxury-border/70 bg-luxury-graphite/50 p-5">
                        <div class="flex items-center gap-3">
                            <p class="shrink-0 text-[11px] font-semibold uppercase tracking-[0.18em] text-luxury-muted">{{ __('Need Help?') }}</p>
                            <span class="h-px flex-1 bg-luxury-border"></span>
                        </div>
                        <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                            @if ($phone)
                                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}"
                                    class="group flex min-h-[3.25rem] items-center gap-3 rounded-xl border border-luxury-border bg-luxury-charcoal px-4 py-3.5 transition
                                           hover:border-blue-400/40 hover:bg-luxury-slate
                                           focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-400/50
                                           active:scale-[0.98]">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-luxury-border bg-luxury-graphite text-blue-400 transition group-hover:border-blue-400/40">
                                        <x-icon name="phone" class="h-4 w-4" />
                                    </span>
                                    <span class="min-w-0 text-left">
                                        <span class="block truncate text-sm font-semibold text-luxury-white">{{ $phone }}</span>
                                        <span class="block text-[11px] text-luxury-muted">{{ __('Call Support') }}</span>
                                    </span>
                                </a>
                            @endif
                            @if ($whatsappDigits)
                                <a href="https://wa.me/{{ $whatsappDigits }}?text={{ rawurlencode($whatsappMessage) }}" target="_blank" rel="noopener"
                                    class="group flex min-h-[3.25rem] items-center gap-3 rounded-xl border border-luxury-border bg-luxury-charcoal px-4 py-3.5 transition
                                           hover:border-emerald-400/40 hover:bg-luxury-slate
                                           focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400/50
                                           active:scale-[0.98]">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-luxury-border bg-luxury-graphite text-emerald-400 transition group-hover:border-emerald-400/40">
                                        <x-icon name="chat" class="h-4 w-4" />
                                    </span>
                                    <span class="min-w-0 text-left">
                                        <span class="block text-sm font-semibold text-luxury-white">{{ __('WhatsApp') }}</span>
                                        <span class="block text-[11px] text-luxury-muted">{{ __('Chat with us') }}</span>
                                    </span>
                                </a>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="relative mt-7 flex justify-center">
                    <a href="{{ route('pages.home') }}"
                        class="tap-scale animate-fade-up delay-4 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-b from-luxury-gold-light to-luxury-gold px-6 py-3.5 text-sm font-semibold text-luxury-black shadow-[0_10px_30px_-10px_rgba(201,162,75,0.5)] transition hover:brightness-105 hover:shadow-[0_14px_36px_-10px_rgba(201,162,75,0.6)] active:scale-[0.98] sm:w-auto">
                        <x-icon name="chevron-left" class="h-4 w-4" />
                        {{ __('Back to Home') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layouts.public>
