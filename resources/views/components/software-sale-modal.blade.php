@php
    // Only ever shown on the software's own sales/demo domain — never on a
    // client's live taxi-booking site. The local-env query param exists
    // purely so this can be previewed/tested without DNS pointed at
    // software.limoschedule.com.
    $isSaleDomain = request()->getHost() === 'software.limoschedule.com'
        || (app()->environment('local') && request()->boolean('preview_sale_modal'));

    // On top of the domain check above, a private, unlinked toggle page
    // (see PromoBannerSettingController) lets the vendor switch this
    // specific promo off without touching code — e.g. while the other one
    // (software-sale-sticky-banner.blade.php) is running instead.
    $isSaleDomain = $isSaleDomain && \App\Models\PromoBannerSetting::current()->sale_modal_enabled;
@endphp

@if ($isSaleDomain)
    @php
        // A deterministic, globally-synchronized 3-day countdown — every
        // visitor at any given moment sees the same "time left," and it
        // repeats forever with zero stored state: just today's timestamp
        // modulo the cycle length.
        $cycleSeconds = 3 * 24 * 60 * 60;
        $nowTimestamp = now()->timestamp;
        $cycleEndsAtMs = ($nowTimestamp + ($cycleSeconds - $nowTimestamp % $cycleSeconds)) * 1000;
    @endphp

    <div
        x-data="softwareSaleModal({{ $cycleSeconds }}, {{ $cycleEndsAtMs }})"
        x-show="open"
        x-cloak
        x-init="init()"
        @keydown.escape.window="close()"
        class="fixed inset-0 z-[90] flex items-center justify-center p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="sale-modal-heading"
    >
        <div x-show="open" x-transition.opacity @click="close()" class="absolute inset-0 bg-black/80 backdrop-blur-sm"></div>

        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            class="relative w-full max-w-lg overflow-hidden rounded-2xl border border-luxury-gold/25 bg-luxury-charcoal shadow-2xl shadow-black/60"
        >
            {{-- Ambient glow, same idiom used elsewhere on the site (errors/404, the homepage CTA). --}}
            <div class="pointer-events-none absolute -right-16 -top-16 h-72 w-72 rounded-full bg-luxury-gold/10 blur-3xl"></div>

            <button type="button" @click="close()" aria-label="{{ __('Close') }}"
                class="absolute end-4 top-4 z-10 flex h-9 w-9 items-center justify-center rounded-full border border-luxury-border bg-luxury-graphite/80 text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                <x-icon name="close" class="h-4 w-4" />
            </button>

            <div class="relative max-h-[90vh] overflow-y-auto px-6 py-8 text-center sm:px-9 sm:py-10">
                <span class="inline-flex items-center gap-1.5 rounded-full border border-luxury-gold/30 bg-luxury-gold/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-wide text-luxury-gold">
                    <span class="relative flex h-1.5 w-1.5">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-luxury-gold opacity-75"></span>
                        <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-luxury-gold"></span>
                    </span>
                    {{ __('Limited-Time Launch Offer') }}
                </span>

                <h2 id="sale-modal-heading" class="mt-4 text-2xl font-bold leading-tight text-luxury-white sm:text-3xl">
                    {{ __('White-Label Taxi Booking Software') }}
                </h2>
                <p class="mx-auto mt-2 max-w-sm text-sm text-luxury-muted">
                    {{ __('The complete platform behind this website — admin panel, customer app, and driver app, fully rebrandable as your own.') }}
                </p>

                {{-- Price --}}
                <div class="mt-6 flex items-center justify-center gap-3">
                    <span class="text-lg text-luxury-muted line-through decoration-red-500 decoration-2">$1,900</span>
                    <span class="text-4xl font-extrabold text-luxury-gold sm:text-5xl">$499</span>
                </div>
                <p class="mt-2 inline-flex items-center gap-1.5 rounded-full border border-luxury-border bg-luxury-graphite/60 px-3 py-1 text-xs font-medium text-luxury-white">
                    <x-icon name="check-circle" class="h-3.5 w-3.5 shrink-0 text-luxury-gold" />
                    {{ __('One-time payment · lifetime access · no monthly or yearly fees') }}
                </p>

                {{-- Countdown --}}
                <div class="mt-7">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-luxury-muted">{{ __('Offer ends in') }}</p>
                    <div class="mx-auto mt-2.5 grid max-w-xs grid-cols-4 gap-2 sm:gap-3">
                        <template x-for="unit in units" :key="unit.label">
                            <div class="rounded-xl border border-luxury-border bg-luxury-graphite/60 py-2.5">
                                <p class="font-mono text-xl font-bold tabular-nums text-luxury-white sm:text-2xl" x-text="unit.value"></p>
                                <p class="mt-0.5 text-[10px] uppercase tracking-wide text-luxury-muted" x-text="unit.label"></p>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- CTAs --}}
                <div class="mt-7 flex flex-col gap-2.5 sm:flex-row">
                    <a href="https://wa.me/923460820722?text={{ rawurlencode(__('Hi! I\'m interested in the white-label taxi booking software.')) }}" target="_blank" rel="noopener"
                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg bg-luxury-gold px-5 py-3 text-sm font-semibold text-luxury-black transition hover:bg-luxury-gold-light active:scale-[0.98]">
                        <x-whatsapp-icon class="h-4 w-4" />
                        {{ __('Chat on WhatsApp') }}
                    </a>
                    <a href="tel:+923460820722"
                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg border border-luxury-border px-5 py-3 text-sm font-medium text-luxury-white transition hover:border-luxury-gold/40">
                        <x-icon name="phone" class="h-4 w-4" />
                        {{ __('Call Now') }}
                    </a>
                </div>

                <a href="mailto:support@limoschedule.com" class="mt-4 inline-flex items-center justify-center gap-1.5 text-xs text-luxury-muted transition hover:text-luxury-gold">
                    <x-icon name="mail" class="h-3.5 w-3.5" />
                    support@limoschedule.com
                </a>
            </div>
        </div>
    </div>

    <script>
        function softwareSaleModal(cycleSeconds, initialEndsAtMs) {
            return {
                open: false,
                endsAt: initialEndsAtMs,
                units: [
                    { label: {{ Illuminate\Support\Js::from(__('Days')) }}, value: '00' },
                    { label: {{ Illuminate\Support\Js::from(__('Hours')) }}, value: '00' },
                    { label: {{ Illuminate\Support\Js::from(__('Minutes')) }}, value: '00' },
                    { label: {{ Illuminate\Support\Js::from(__('Seconds')) }}, value: '00' },
                ],
                timer: null,

                init() {
                    // Closing only dismisses the current page view — a reload
                    // or a fresh visit should always show the offer again.
                    setTimeout(() => { this.open = true; }, 900);

                    this.tick();
                    this.timer = setInterval(() => this.tick(), 1000);
                },

                tick() {
                    // Self-healing: if the cycle boundary has passed (including
                    // while this tab sat idle through more than one reset),
                    // keep rolling it forward rather than showing a negative
                    // countdown — this is what makes it repeat every 3 days
                    // forever with no server round-trip.
                    while (Date.now() >= this.endsAt) {
                        this.endsAt += cycleSeconds * 1000;
                    }

                    const remainingMs = this.endsAt - Date.now();
                    const totalSeconds = Math.max(0, Math.floor(remainingMs / 1000));
                    const pad = (n) => String(n).padStart(2, '0');

                    this.units[0].value = pad(Math.floor(totalSeconds / 86400));
                    this.units[1].value = pad(Math.floor((totalSeconds % 86400) / 3600));
                    this.units[2].value = pad(Math.floor((totalSeconds % 3600) / 60));
                    this.units[3].value = pad(totalSeconds % 60);
                },

                close() {
                    this.open = false;
                },
            };
        }
    </script>
@endif
