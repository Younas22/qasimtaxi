@php
    // Same domain gating as software-sale-modal.blade.php, plus its own
    // independent toggle — see PromoBannerSetting. The two promos are meant
    // to be run one at a time (or both, or neither), never assumed to be
    // mutually exclusive in code.
    $isSaleDomain = request()->getHost() === 'software.limoschedule.com'
        || (app()->environment('local') && request()->boolean('preview_sale_modal'));

    $isSaleDomain = $isSaleDomain && \App\Models\PromoBannerSetting::current()->sticky_banner_enabled;
@endphp

@if ($isSaleDomain)
    {{-- Desktop-only (matches x-sticky-booking-button / x-sticky-whatsapp-button —
         mobile's bottom edge already belongs to x-bottom-nav). Stacked above the
         "Book Now" pill in the same bottom-end corner, rather than sharing its row
         or spanning the width of the page, per the "leave a column's worth of space
         from the side" brief — this reads as a corner card, not a bar. --}}
    <div
        x-data="{ open: true }"
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        class="fixed bottom-28 end-8 z-30 hidden w-full max-w-xs lg:block"
    >
        <div class="relative overflow-hidden rounded-2xl border border-luxury-gold/25 bg-luxury-charcoal p-5 shadow-2xl shadow-black/40">
            <div class="pointer-events-none absolute -end-10 -top-10 h-32 w-32 rounded-full bg-luxury-gold/10 blur-2xl"></div>

            <button type="button" @click="open = false" aria-label="{{ __('Dismiss') }}"
                class="absolute end-3 top-3 flex h-7 w-7 items-center justify-center rounded-full text-luxury-muted transition hover:text-luxury-gold">
                <x-icon name="close" class="h-3.5 w-3.5" />
            </button>

            <p class="relative pe-6 text-sm leading-relaxed text-luxury-white">
                {{ __('This is white-label software — it comes with the website, customer, driver, and admin panels, with fare estimation, the booking form, and PayPal & Stripe payments all fully activated.') }}
            </p>

            <div class="relative mt-4 flex items-center gap-2.5">
                <a href="https://limoschedule.com/pricing" target="_blank" rel="noopener"
                    class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg bg-luxury-gold px-4 py-2.5 text-xs font-semibold text-luxury-black transition hover:bg-luxury-gold-light active:scale-[0.98]">
                    {{ __('Price') }}
                </a>
                <button type="button" @click="open = false"
                    class="inline-flex items-center justify-center rounded-lg border border-luxury-border px-4 py-2.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-white">
                    {{ __('Hide') }}
                </button>
            </div>
        </div>
    </div>
@endif
