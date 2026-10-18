{{--
    Assumes it renders within a parent scope exposing Alpine state `pricingOpen`
    (declared once on layouts.public, alongside sidebarOpen/searchOpen).
--}}

@php
    $globalPricing = \App\Models\PricingRule::global();
    $formatTime = fn ($value) => $value ? \Illuminate\Support\Carbon::parse($value)->format(app()->getLocale() === 'en' ? 'g:i A' : 'H:i') : null;

    $priceSections = [
        [
            'title' => __('Base Pricing'),
            'rows' => [
                [__('Base Fare'), currency($globalPricing->base_fare)],
                [__('0–50 km'), currency($globalPricing->km_fare)],
                $globalPricing->mid_distance_km_fare ? [__('50–100 km'), currency($globalPricing->mid_distance_km_fare)] : null,
                $globalPricing->long_distance_km_fare ? [__('100–200 km'), currency($globalPricing->long_distance_km_fare)] : null,
                $globalPricing->very_long_distance_km_fare ? [__('200+ km'), currency($globalPricing->very_long_distance_km_fare)] : null,
                [__('Per Hour (hourly bookings)'), currency($globalPricing->hour_fare)],
            ],
        ],
        [
            'title' => __('Included in Base Fare'),
            'rows' => [
                [__('Minimum Fare'), currency($globalPricing->minimum_fare)],
                $globalPricing->included_km > 0 ? [__('Included KM'), rtrim(rtrim((string) $globalPricing->included_km, '0'), '.').' km'] : null,
                $globalPricing->included_hours > 0 ? [__('Included Hours'), rtrim(rtrim((string) $globalPricing->included_hours, '0'), '.').' h'] : null,
                [__('Included Passengers'), (int) $globalPricing->included_passengers],
                $globalPricing->extra_passenger_charge > 0 ? [__('Extra Passenger Charge'), currency($globalPricing->extra_passenger_charge)] : null,
            ],
        ],
        [
            'title' => __('Waiting & Extra Charges'),
            'rows' => [
                [__('Waiting Charge (per minute)'), currency($globalPricing->waiting_charge_per_minute)],
                $globalPricing->free_waiting_minutes > 0 ? [__('Free Waiting Minutes'), $globalPricing->free_waiting_minutes.' '.__('min')] : null,
                $globalPricing->night_charge > 0 ? [__('Night Charge (:start – :end)', ['start' => $formatTime($globalPricing->night_start_time), 'end' => $formatTime($globalPricing->night_end_time)]), currency($globalPricing->night_charge)] : null,
                $globalPricing->weekend_charge > 0 ? [__('Weekend Charge'), currency($globalPricing->weekend_charge)] : null,
                $globalPricing->approach_km_fare > 0 ? [__('Pickup Distance Charge (beyond :km km, per km)', ['km' => rtrim(rtrim((string) $globalPricing->approach_free_km, '0'), '.')]), currency($globalPricing->approach_km_fare)] : null,
            ],
        ],
        [
            'title' => __('Surcharges & Fees'),
            'rows' => [
                $globalPricing->toll_charge > 0 ? [__('Toll Charge'), currency($globalPricing->toll_charge)] : null,
                $globalPricing->airport_surcharge > 0 ? [__('Airport Surcharge'), currency($globalPricing->airport_surcharge)] : null,
                $globalPricing->service_fee > 0 ? [__('Service Fee'), currency($globalPricing->service_fee)] : null,
            ],
        ],
    ];
@endphp

<div x-show="pricingOpen" x-cloak
    class="fixed inset-0 z-50"
    x-trap.noscroll="pricingOpen"
    @keydown.escape.window="pricingOpen = false">

    <div x-show="pricingOpen" x-transition.opacity @click="pricingOpen = false" class="fixed inset-0 bg-black/70"></div>

    <div x-show="pricingOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        class="theme-dark-scope relative mx-auto mt-0 flex max-h-screen w-full flex-col overflow-hidden bg-luxury-black sm:mt-12 sm:max-h-[calc(100vh-6rem)] sm:max-w-md sm:rounded-2xl sm:border sm:border-luxury-border sm:shadow-2xl">

        <div class="flex items-center gap-3 border-b border-luxury-border px-5 py-4">
            <x-icon name="cash" class="h-5 w-5 shrink-0 text-luxury-gold" />
            <h2 class="text-sm font-semibold text-luxury-white">{{ __('Pricing') }}</h2>
            <button type="button" @click="pricingOpen = false" aria-label="{{ __('Close pricing') }}"
                class="ms-auto flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-luxury-muted hover:bg-luxury-graphite hover:text-luxury-white">
                <x-icon name="close" class="h-4 w-4" />
            </button>
        </div>

        <div class="scrollbar-luxury flex-1 overflow-y-auto px-5 py-5">
            {{-- Contact --}}
            @if (setting('phone') || setting('address'))
                <div class="mb-5 space-y-2.5 rounded-xl border border-luxury-border bg-luxury-charcoal p-4">
                    @if (setting('phone'))
                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', setting('phone')) }}"
                            class="flex items-center gap-2.5 text-sm font-bold text-luxury-white transition hover:text-luxury-gold">
                            <x-icon name="phone" class="h-4 w-4 shrink-0 text-luxury-gold" />
                            {{ setting('phone') }}
                        </a>
                    @endif
                    @if (setting('address'))
                        <div class="flex items-start gap-2.5 text-sm text-luxury-muted">
                            <x-icon name="map-pin" class="mt-0.5 h-4 w-4 shrink-0 text-luxury-gold" />
                            <span>{{ setting('address') }}</span>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Price chart --}}
            <div class="space-y-5">
                @foreach ($priceSections as $section)
                    @php $rows = array_values(array_filter($section['rows'])); @endphp
                    @continue(empty($rows))
                    <div>
                        <p class="mb-2 text-xs font-medium uppercase tracking-wider text-luxury-muted">{{ $section['title'] }}</p>
                        <div class="divide-y divide-luxury-border overflow-hidden rounded-xl border border-luxury-border">
                            @foreach ($rows as [$label, $value])
                                <div class="flex items-center justify-between gap-4 bg-luxury-charcoal px-4 py-2.5 text-sm">
                                    <span class="text-luxury-muted">{{ $label }}</span>
                                    <span class="shrink-0 font-semibold text-luxury-white">{{ $value }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <p class="mt-5 text-xs text-luxury-muted">{{ __('Final fare may vary based on route, traffic, and options selected at booking.') }}</p>
        </div>

        <div class="border-t border-luxury-border p-4" style="padding-bottom: calc(1rem + env(safe-area-inset-bottom))">
            <a href="{{ route('pages.home') }}" @click="pricingOpen = false"
                class="flex w-full items-center justify-center rounded-lg bg-luxury-gold px-4 py-2.5 text-sm font-semibold text-luxury-black transition hover:bg-luxury-gold-light">
                {{ __('Book Now') }}
            </a>
        </div>
    </div>
</div>
