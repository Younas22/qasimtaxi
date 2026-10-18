@props(['class' => ''])

@php
    $settings = booking_setting();
    $websiteBookingEnabled = (bool) ($settings->website_booking_enabled && $settings->guest_booking_enabled);
@endphp

@if ($websiteBookingEnabled)
@php
    $categories = \App\Models\VehicleCategory::active()->ordered()->get(['id', 'name', 'description', 'image'])
        ->map(fn ($category) => [
            'id' => $category->id,
            'name' => $category->name,
            'description' => $category->description,
            'image_url' => $category->image_url,
        ])->values();
    $voiceSearchEnabled = (bool) $settings->voice_search_enabled;
    // Surfaced as clickable suggestions next to the coupon field — a coupon
    // is otherwise invisible to a visitor who doesn't already know the
    // code. Still just a suggestion: whichever one they click goes through
    // the exact same applyCoupon() check as typing it by hand, so a code
    // that turns out not to meet its minimum fare at the current quote
    // still gets rejected there, not here.
    $availableCoupons = \App\Models\Coupon::active()->orderByDesc('id')->limit(6)->get()
        ->map(fn ($coupon) => ['code' => $coupon->code, 'label' => $coupon->discount_label])
        ->values();
    $initialStops = old('stops', []);
    $initialReturnStops = old('return_stops', []);
    $serviceTypes = collect(\App\Models\Booking::TYPES)->map(fn ($label, $value) => ['value' => $value, 'label' => $label])->values();
    $googleMapsKey = config('services.google_maps.key');
    $dialCodes = \App\Models\DialCode::query()
        ->whereNotNull('iso2')
        ->whereNotNull('phone_code')
        ->where('phone_code', '!=', '')
        ->orderBy('name')
        ->get(['name', 'iso2', 'phone_code'])
        ->map(fn ($country) => [
            'name' => $country->name,
            'iso2' => strtolower($country->iso2),
            'dial' => $country->dial,
        ])->values();
    $defaultDialCode = setting('default_country')
        ? \App\Models\DialCode::where('iso2', setting('default_country'))->value('phone_code')
        : null;
    $defaultDialCode = $defaultDialCode ? '+'.$defaultDialCode : '+1';

    // "Right now" in the business's own configured timezone (not the
    // visitor's browser clock), rounded up to the nearest half-hour slot —
    // used to prefill the pickup date/time when nothing else is prefilled.
    $businessNow = now(setting('timezone', config('app.timezone')));
    $defaultDate = $businessNow->format('Y-m-d');
    $defaultSlot = $businessNow->minute <= 30
        ? $businessNow->copy()->minute(30)->second(0)
        : $businessNow->copy()->addHour()->minute(0)->second(0);
    if ($defaultSlot->format('Y-m-d') !== $defaultDate) {
        $defaultSlot = $businessNow->copy()->hour(23)->minute(30)->second(0);
    }
    $defaultTime = $defaultSlot->format('H:i');
@endphp

@once
    @if ($googleMapsKey)
        <script async src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsKey }}&libraries=places&callback=__initBookingWidgetMaps"></script>
        <script>
            window.__initBookingWidgetMaps = function () {
                const attach = () => {
                    document.querySelectorAll('[data-booking-widget]').forEach((el) => {
                        Alpine.$data(el).initAutocomplete();
                    });
                };

                // Google's script is async and may finish loading before or
                // after Alpine has processed this element's x-data — handle
                // both orderings rather than assuming Alpine won the race.
                if (window.Alpine) {
                    attach();
                } else {
                    document.addEventListener('alpine:initialized', attach);
                }
            };
        </script>
    @endif
@endonce

<style>
    /* Browsers don't show a pointer/hand cursor on <button> by default (unlike <a>) —
       every interactive control in this widget is a <button>, so without this the
       cursor never changes on hover anywhere in the card. */
    #booking-widget button:not(:disabled) {
        cursor: pointer;
    }

    /* Slim, theme-colored scrollbars for the dropdown lists (Vehicle Category,
       Phone country list, Trip Type) instead of the bulky default OS scrollbar
       with up/down arrow buttons. */
    #booking-widget * {
        scrollbar-width: thin;
        scrollbar-color: color-mix(in srgb, var(--color-luxury-gold) 45%, transparent) transparent;
    }
    #booking-widget *::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    #booking-widget *::-webkit-scrollbar-track {
        background: transparent;
    }
    #booking-widget *::-webkit-scrollbar-thumb {
        background-color: color-mix(in srgb, var(--color-luxury-gold) 45%, transparent);
        border-radius: 9999px;
    }
    #booking-widget *::-webkit-scrollbar-thumb:hover {
        background-color: color-mix(in srgb, var(--color-luxury-gold) 65%, transparent);
    }
</style>

<p class="mb-3 text-center text-lg font-semibold text-luxury-white sm:text-xl">
    {{ __('Book Ride & Fare Estimate') }}
</p>

<div id="booking-widget" data-booking-widget
    @select-vehicle-category.window="vehicleCategory = $event.detail"
    @select-route.window="pickup = $event.detail.pickup; dropoff = $event.detail.dropoff"
    @keydown.escape="tripTypeOpen = false; vehicleOpen = false; phoneCodeOpen = false"
    x-data="bookingSearchBox({
        categories: {{ \Illuminate\Support\Js::from($categories) }},
        serviceTypes: {{ \Illuminate\Support\Js::from($serviceTypes) }},
        dialCodes: {{ \Illuminate\Support\Js::from($dialCodes) }},
        quoteUrl: {{ \Illuminate\Support\Js::from(route('booking.quote')) }},
        availableCoupons: {{ \Illuminate\Support\Js::from($availableCoupons) }},
        whatsappNumber: {{ \Illuminate\Support\Js::from(setting('whatsapp')) }},
        currencySymbol: {{ \Illuminate\Support\Js::from(active_currency()?->symbol ?? '$') }},
        currencyRate: {{ \Illuminate\Support\Js::from((float) (active_currency()?->exchange_rate ?? 1)) }},
        defaultDialCode: {{ \Illuminate\Support\Js::from($defaultDialCode) }},
        defaultDate: {{ \Illuminate\Support\Js::from($defaultDate) }},
        defaultTime: {{ \Illuminate\Support\Js::from($defaultTime) }},
        initial: {
            {{-- 'Rebook' links (from a past trip) prefill via query string; normal
                 validation-error redisplay prefills via old() — old() wins if both
                 are somehow present, since it reflects the user's own last input. --}}
            pickup: {{ \Illuminate\Support\Js::from(old('pickup_location', request()->query('pickup'))) }},
            dropoff: {{ \Illuminate\Support\Js::from(old('dropoff_location', request()->query('dropoff'))) }},
            date: {{ \Illuminate\Support\Js::from(old('pickup_date')) }},
            time: {{ \Illuminate\Support\Js::from(old('pickup_time')) }},
            passengers: {{ \Illuminate\Support\Js::from((int) old('passengers', 1)) }},
            luggage: {{ \Illuminate\Support\Js::from((int) old('luggage', 0)) }},
            vehicleCategory: {{ \Illuminate\Support\Js::from(old('vehicle_category_id', request()->query('vehicle_category', ''))) }},
            type: {{ \Illuminate\Support\Js::from(old('type', 'one_way')) }},
            hours: {{ \Illuminate\Support\Js::from((int) old('hours', 2)) }},
            returnDate: {{ \Illuminate\Support\Js::from(old('return_date')) }},
            returnTime: {{ \Illuminate\Support\Js::from(old('return_time')) }},
            stops: {{ \Illuminate\Support\Js::from(!empty($initialStops) ? array_values($initialStops) : []) }},
            returnPickup: {{ \Illuminate\Support\Js::from(old('return_pickup_location')) }},
            returnDropoff: {{ \Illuminate\Support\Js::from(old('return_dropoff_location')) }},
            returnStops: {{ \Illuminate\Support\Js::from(!empty($initialReturnStops) ? array_values($initialReturnStops) : []) }},
            name: {{ \Illuminate\Support\Js::from(old('name')) }},
            email: {{ \Illuminate\Support\Js::from(old('email')) }},
            phone: {{ \Illuminate\Support\Js::from(old('phone')) }},
        },
    })"
    {{ $attributes->merge(['class' => 'relative rounded-3xl border border-luxury-border bg-luxury-black p-4 shadow-2xl shadow-black/40 sm:p-6 '.$class]) }}>

    @if (session('error'))
        <div class="mb-4 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-400">
            {{ session('error') }}
        </div>
    @endif

    {{-- Trip Type — compact pill, top-left --}}
    <div class="mb-4">
        <div class="relative isolate inline-block" :class="tripTypeOpen ? 'z-30' : 'z-20'" @click.outside="tripTypeOpen = false">
            <button type="button" @click="tripTypeOpen = !tripTypeOpen" aria-haspopup="listbox" :aria-expanded="tripTypeOpen"
                class="flex items-center gap-2 rounded-full border border-luxury-border bg-luxury-graphite/60 py-1.5 ps-3 pe-2 text-[11px] font-semibold text-luxury-white transition hover:border-luxury-gold/40 focus:outline-none focus-visible:ring-2 focus-visible:ring-luxury-gold/50">
                <x-icon name="car" class="h-3 w-3 text-luxury-gold" />
                <span x-text="typeLabel()"></span>
                <x-icon name="chevron-down" class="h-3 w-3 text-luxury-muted transition-transform" x-bind:class="{ 'rotate-180': tripTypeOpen }" />
            </button>

            <div x-show="tripTypeOpen" x-cloak
                x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                role="listbox" class="absolute start-0 top-full z-50 mt-2 w-56 overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal py-1.5 shadow-2xl shadow-black/50">
                <template x-for="option in serviceTypes" :key="option.value">
                    <button type="button" role="option" :aria-selected="type === option.value" @click="type = option.value; tripTypeOpen = false"
                        class="flex w-full items-center justify-between gap-2 px-3 py-2 text-start text-sm transition hover:bg-luxury-graphite"
                        :class="type === option.value ? 'text-luxury-gold' : 'text-luxury-muted'">
                        <span x-text="option.label"></span>
                        <x-icon name="check-circle" class="h-3.5 w-3.5" x-show="type === option.value" x-cloak />
                    </button>
                </template>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('booking.store') }}" @submit="handleSubmit($event)" class="space-y-5">
        @csrf
        <input type="hidden" name="type" :value="type">

        {{-- Outbound route --}}
        <div class="relative space-y-3 ps-1">
            <div class="pointer-events-none absolute start-[9px] top-4 bottom-4 w-px bg-luxury-border"></div>

            {{-- Pickup --}}
            <div class="relative flex items-start gap-3">
                <span class="relative z-10 mt-4 h-4 w-4 shrink-0 rounded-full border-2 border-luxury-gold bg-luxury-black"></span>
                <div class="relative flex-1">
                    <input type="text" id="pickup_location" name="pickup_location" x-ref="pickupInput" x-model="pickup" required placeholder=" "
                        @input="pickupLat = null; pickupLng = null; pickupPlaceId = null"
                        class="peer w-full rounded-xl border border-luxury-border bg-luxury-black/40 px-4 pb-2 pt-5 text-sm text-luxury-white placeholder-transparent transition focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                    <label for="pickup_location" class="pointer-events-none absolute start-4 top-1/2 -translate-y-1/2 text-sm text-luxury-muted transition-all duration-150 peer-focus:top-3.5 peer-focus:text-[11px] peer-focus:text-luxury-gold peer-[:not(:placeholder-shown)]:top-3.5 peer-[:not(:placeholder-shown)]:text-[11px]">
                        {{ __('Pick-Up Location') }}
                    </label>
                    @if ($voiceSearchEnabled)
                        <button type="button" @click="startVoiceSearch" aria-label="{{ __('Voice search') }}"
                            class="absolute end-2 top-1/2 flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-lg text-luxury-muted transition hover:bg-luxury-graphite hover:text-luxury-gold">
                            <x-icon name="mic" class="h-3.5 w-3.5" />
                        </button>
                    @endif
                    <p class="mt-1 text-xs text-red-400">{{ $errors->first('pickup_location') }}</p>
                </div>
            </div>

            {{-- Stops --}}
            <template x-for="(stop, index) in stops" :key="'stop-'+index">
                <div class="relative flex items-start gap-3" x-transition>
                    <span class="relative z-10 mt-4 ms-[3px] h-2.5 w-2.5 shrink-0 rounded-full bg-luxury-muted"></span>
                    <div class="relative flex-1">
                        <input type="text" :name="'stops[' + index + '][location]'" x-model="stops[index].location" placeholder=" "
                            x-init="initStopAutocomplete($el, index, false)"
                            @input="stops[index].lat = null; stops[index].lng = null; stops[index].placeId = null"
                            class="peer w-full rounded-xl border border-luxury-border bg-luxury-black/40 px-4 pb-2 pt-5 text-sm text-luxury-white placeholder-transparent transition focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                        <input type="hidden" :name="'stops[' + index + '][lat]'" :value="stops[index].lat ?? ''">
                        <input type="hidden" :name="'stops[' + index + '][lng]'" :value="stops[index].lng ?? ''">
                        <input type="hidden" :name="'stops[' + index + '][place_id]'" :value="stops[index].placeId ?? ''">
                        <label class="pointer-events-none absolute start-4 top-1/2 -translate-y-1/2 text-sm text-luxury-muted transition-all duration-150 peer-focus:top-3.5 peer-focus:text-[11px] peer-focus:text-luxury-gold peer-[:not(:placeholder-shown)]:top-3.5 peer-[:not(:placeholder-shown)]:text-[11px]"
                            x-text="'{{ __('Stop') }} ' + (index + 1)"></label>
                        <button type="button" @click="stops.splice(index, 1)" aria-label="{{ __('Remove stop') }}"
                            class="absolute end-2 top-1/2 flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-lg text-luxury-muted transition hover:bg-red-500/10 hover:text-red-400">
                            <x-icon name="close" class="h-3.5 w-3.5" />
                        </button>
                    </div>
                </div>
            </template>

            {{-- Drop-off --}}
            <div class="relative flex items-start gap-3">
                <span class="relative z-10 mt-4 h-4 w-4 shrink-0 rounded-full border-2 border-red-400 bg-luxury-black"></span>
                <div class="relative flex-1">
                    <input type="text" id="dropoff_location" name="dropoff_location" x-ref="dropoffInput" x-model="dropoff" :required="type !== 'hourly'" placeholder=" "
                        @input="dropoffLat = null; dropoffLng = null; dropoffPlaceId = null"
                        class="peer w-full rounded-xl border border-luxury-border bg-luxury-black/40 px-4 pb-2 pt-5 text-sm text-luxury-white placeholder-transparent transition focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                    <label for="dropoff_location" class="pointer-events-none absolute start-4 top-1/2 -translate-y-1/2 text-sm text-luxury-muted transition-all duration-150 peer-focus:top-3.5 peer-focus:text-[11px] peer-focus:text-luxury-gold peer-[:not(:placeholder-shown)]:top-3.5 peer-[:not(:placeholder-shown)]:text-[11px]">
                        {{ __('Drop-off Location') }}
                    </label>
                    <p class="mt-1 text-xs text-red-400">{{ $errors->first('dropoff_location') }}</p>
                </div>
            </div>

            <button type="button" @click="stops.push({ location: '', lat: null, lng: null, placeId: null })" class="ms-7 text-xs font-medium text-luxury-gold hover:text-luxury-gold-light">
                + {{ __('Add Stop') }}
            </button>
        </div>

        {{-- Pick-Up Date / Pick-Up Time — custom Material-style pickers --}}
        <div class="grid grid-cols-2 gap-3">
            <div class="relative isolate" :class="(datePickerOpen && datePickerField === 'date') ? 'z-30' : 'z-20'">
                <label class="mb-1 flex items-center gap-1 text-[11px] font-medium text-luxury-muted">
                    <x-icon name="calendar" class="h-3 w-3" />
                    {{ __('Pick-Up Date') }}
                </label>
                <button type="button" @click="openDatePicker('date')"
                    class="flex w-full items-center gap-2 rounded-xl border border-luxury-border bg-luxury-black/40 px-3 py-2.5 text-start transition hover:border-luxury-gold/40 focus:outline-none focus-visible:ring-2 focus-visible:ring-luxury-gold/50">
                    <x-icon name="calendar" class="h-3.5 w-3.5 shrink-0 text-luxury-muted" />
                    <span class="flex-1 truncate text-sm text-luxury-white" x-text="formatDateDisplay('date') || '{{ __('Select date') }}'"></span>
                </button>
                <input type="hidden" name="pickup_date" :value="date">

                <div x-show="datePickerOpen && datePickerField === 'date'" x-cloak @click.outside="datePickerOpen = false"
                    x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                    class="absolute start-0 top-full z-50 mt-2 w-72 rounded-2xl border border-luxury-border bg-luxury-charcoal p-3 shadow-2xl shadow-black/50">
                    <div class="mb-2 flex items-center justify-between">
                        <button type="button" @click="calendarPrevMonth()" aria-label="{{ __('Previous month') }}"
                            class="flex h-7 w-7 items-center justify-center rounded-lg text-luxury-muted transition hover:bg-luxury-graphite hover:text-luxury-white">
                            <x-icon name="chevron-left" class="h-3.5 w-3.5" />
                        </button>
                        <span class="text-sm font-semibold text-luxury-white" x-text="calendarMonthLabel()"></span>
                        <button type="button" @click="calendarNextMonth()" aria-label="{{ __('Next month') }}"
                            class="flex h-7 w-7 items-center justify-center rounded-lg text-luxury-muted transition hover:bg-luxury-graphite hover:text-luxury-white">
                            <x-icon name="chevron-right" class="h-3.5 w-3.5" />
                        </button>
                    </div>
                    <div class="grid grid-cols-7 gap-1 text-center text-[10px] font-medium text-luxury-muted">
                        <template x-for="wd in weekdayLabels()" :key="wd"><span x-text="wd"></span></template>
                    </div>
                    <div class="mt-1 grid grid-cols-7 gap-1">
                        <template x-for="(cell, idx) in calendarDays()" :key="idx">
                            <button type="button" x-show="cell" :disabled="cell?.disabled" @click="cell && selectDate(cell.dateStr)"
                                class="flex h-8 w-8 items-center justify-center rounded-full text-xs transition"
                                :class="{
                                    'bg-luxury-gold text-luxury-black font-semibold': cell?.selected,
                                    'text-luxury-gold ring-1 ring-luxury-gold': cell?.today && !cell?.selected,
                                    'text-luxury-white hover:bg-luxury-graphite': cell && !cell.selected && !cell.disabled && !cell.today,
                                    'text-luxury-muted/30': cell?.disabled,
                                }"
                                x-text="cell?.day"></button>
                        </template>
                    </div>
                </div>
                <p class="mt-1 text-xs text-red-400">{{ $errors->first('pickup_date') }}</p>
            </div>

            <div class="relative isolate" :class="(timePickerOpen && timePickerField === 'time') ? 'z-30' : 'z-20'">
                <label class="mb-1 flex items-center gap-1 text-[11px] font-medium text-luxury-muted">
                    <x-icon name="clock" class="h-3 w-3" />
                    {{ __('Pick-Up Time') }}
                </label>
                <button type="button" @click="openTimePicker('time')"
                    class="flex w-full items-center gap-2 rounded-xl border border-luxury-border bg-luxury-black/40 px-3 py-2.5 text-start transition hover:border-luxury-gold/40 focus:outline-none focus-visible:ring-2 focus-visible:ring-luxury-gold/50">
                    <x-icon name="clock" class="h-3.5 w-3.5 shrink-0 text-luxury-muted" />
                    <span class="flex-1 truncate text-sm text-luxury-white" x-text="formatTimeDisplay('time') || '{{ __('Select time') }}'"></span>
                </button>
                <input type="hidden" name="pickup_time" :value="time">

                <div x-show="timePickerOpen && timePickerField === 'time'" x-cloak @click.outside="timePickerOpen = false"
                    x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                    class="absolute start-0 top-full z-50 mt-2 max-h-64 w-40 overflow-y-auto rounded-2xl border border-luxury-border bg-luxury-charcoal p-1.5 shadow-2xl shadow-black/50">
                    <template x-for="slot in timeSlots" :key="slot">
                        <button type="button" @click="selectTime(slot)"
                            class="flex w-full items-center rounded-lg px-3 py-2 text-start text-sm transition hover:bg-luxury-graphite"
                            :class="isTimeSelected(slot) ? 'text-luxury-gold font-medium' : 'text-luxury-white'"
                            x-text="formatTimeLabel(slot)"></button>
                    </template>
                </div>
                <p class="mt-1 text-xs text-red-400">{{ $errors->first('pickup_time') }}</p>
            </div>
        </div>

        {{-- Hourly: Number of Hours --}}
        <div x-show="type === 'hourly'" x-cloak x-transition class="max-w-[12rem]">
            <label class="mb-1 flex items-center gap-1 text-[11px] font-medium text-luxury-muted">
                <x-icon name="clock" class="h-3 w-3" />
                {{ __('Number of Hours') }}
            </label>
            <input type="number" name="hours" x-model.number="hours" min="1" max="24"
                class="w-full rounded-xl border border-luxury-border bg-luxury-black/40 px-3 py-2.5 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
            <p class="mt-1 text-xs text-red-400">{{ $errors->first('hours') }}</p>
        </div>

        {{-- Round Trip: Return Journey --}}
        <div x-show="type === 'round_trip'" x-cloak
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 -translate-y-2"
            class="space-y-4 rounded-2xl border border-luxury-border bg-luxury-graphite/30 p-4">
            <p class="flex items-center justify-center gap-2 text-center text-xs font-semibold uppercase tracking-wide text-luxury-gold">
                <x-icon name="arrow-down" class="h-3 w-3" />
                {{ __('Return Journey Details') }}
            </p>

            <div class="relative space-y-3 ps-1">
                <div class="pointer-events-none absolute start-[9px] top-4 bottom-4 w-px bg-luxury-border"></div>

                {{-- Return Pickup --}}
                <div class="relative flex items-start gap-3">
                    <span class="relative z-10 mt-4 h-4 w-4 shrink-0 rounded-full border-2 border-luxury-gold bg-luxury-black"></span>
                    <div class="relative flex-1">
                        <input type="text" id="return_pickup_location" name="return_pickup_location" x-ref="returnPickupInput" x-model="returnPickup" :required="type === 'round_trip'" placeholder=" "
                            @input="returnPickupLat = null; returnPickupLng = null; returnPickupPlaceId = null"
                            class="peer w-full rounded-xl border border-luxury-border bg-luxury-black/40 px-4 pb-2 pt-5 text-sm text-luxury-white placeholder-transparent transition focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                        <label for="return_pickup_location" class="pointer-events-none absolute start-4 top-1/2 -translate-y-1/2 text-sm text-luxury-muted transition-all duration-150 peer-focus:top-3.5 peer-focus:text-[11px] peer-focus:text-luxury-gold peer-[:not(:placeholder-shown)]:top-3.5 peer-[:not(:placeholder-shown)]:text-[11px]">
                            {{ __('Return Pickup Location') }}
                        </label>
                        <p class="mt-1 text-xs text-red-400">{{ $errors->first('return_pickup_location') }}</p>
                    </div>
                </div>

                {{-- Return Stops --}}
                <template x-for="(stop, index) in returnStops" :key="'return-stop-'+index">
                    <div class="relative flex items-start gap-3" x-transition>
                        <span class="relative z-10 mt-4 ms-[3px] h-2.5 w-2.5 shrink-0 rounded-full bg-luxury-muted"></span>
                        <div class="relative flex-1">
                            <input type="text" :name="'return_stops[' + index + '][location]'" x-model="returnStops[index].location" placeholder=" "
                                x-init="initStopAutocomplete($el, index, true)"
                                @input="returnStops[index].lat = null; returnStops[index].lng = null; returnStops[index].placeId = null"
                                class="peer w-full rounded-xl border border-luxury-border bg-luxury-black/40 px-4 pb-2 pt-5 text-sm text-luxury-white placeholder-transparent transition focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                            <input type="hidden" :name="'return_stops[' + index + '][lat]'" :value="returnStops[index].lat ?? ''">
                            <input type="hidden" :name="'return_stops[' + index + '][lng]'" :value="returnStops[index].lng ?? ''">
                            <input type="hidden" :name="'return_stops[' + index + '][place_id]'" :value="returnStops[index].placeId ?? ''">
                            <label class="pointer-events-none absolute start-4 top-1/2 -translate-y-1/2 text-sm text-luxury-muted transition-all duration-150 peer-focus:top-3.5 peer-focus:text-[11px] peer-focus:text-luxury-gold peer-[:not(:placeholder-shown)]:top-3.5 peer-[:not(:placeholder-shown)]:text-[11px]"
                                x-text="'{{ __('Stop') }} ' + (index + 1)"></label>
                            <button type="button" @click="returnStops.splice(index, 1)" aria-label="{{ __('Remove stop') }}"
                                class="absolute end-2 top-1/2 flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-lg text-luxury-muted transition hover:bg-red-500/10 hover:text-red-400">
                                <x-icon name="close" class="h-3.5 w-3.5" />
                            </button>
                        </div>
                    </div>
                </template>

                {{-- Return Drop-off --}}
                <div class="relative flex items-start gap-3">
                    <span class="relative z-10 mt-4 h-4 w-4 shrink-0 rounded-full border-2 border-red-400 bg-luxury-black"></span>
                    <div class="relative flex-1">
                        <input type="text" id="return_dropoff_location" name="return_dropoff_location" x-ref="returnDropoffInput" x-model="returnDropoff" :required="type === 'round_trip'" placeholder=" "
                            @input="returnDropoffLat = null; returnDropoffLng = null; returnDropoffPlaceId = null"
                            class="peer w-full rounded-xl border border-luxury-border bg-luxury-black/40 px-4 pb-2 pt-5 text-sm text-luxury-white placeholder-transparent transition focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                        <label for="return_dropoff_location" class="pointer-events-none absolute start-4 top-1/2 -translate-y-1/2 text-sm text-luxury-muted transition-all duration-150 peer-focus:top-3.5 peer-focus:text-[11px] peer-focus:text-luxury-gold peer-[:not(:placeholder-shown)]:top-3.5 peer-[:not(:placeholder-shown)]:text-[11px]">
                            {{ __('Return Drop-off Location') }}
                        </label>
                        <p class="mt-1 text-xs text-red-400">{{ $errors->first('return_dropoff_location') }}</p>
                    </div>
                </div>

                <button type="button" @click="returnStops.push({ location: '', lat: null, lng: null, placeId: null })" class="ms-7 text-xs font-medium text-luxury-gold hover:text-luxury-gold-light">
                    + {{ __('Add Stop') }}
                </button>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="relative isolate" :class="(datePickerOpen && datePickerField === 'returnDate') ? 'z-30' : 'z-20'">
                    <label class="mb-1 flex items-center gap-1 text-[11px] font-medium text-luxury-muted">
                        <x-icon name="calendar" class="h-3 w-3" />
                        {{ __('Return Date') }}
                    </label>
                    <button type="button" @click="openDatePicker('returnDate')"
                        class="flex w-full items-center gap-2 rounded-xl border border-luxury-border bg-luxury-black/40 px-3 py-2.5 text-start transition hover:border-luxury-gold/40 focus:outline-none focus-visible:ring-2 focus-visible:ring-luxury-gold/50">
                        <x-icon name="calendar" class="h-3.5 w-3.5 shrink-0 text-luxury-muted" />
                        <span class="flex-1 truncate text-sm text-luxury-white" x-text="formatDateDisplay('returnDate') || '{{ __('Select date') }}'"></span>
                    </button>
                    <input type="hidden" name="return_date" :value="returnDate">

                    <div x-show="datePickerOpen && datePickerField === 'returnDate'" x-cloak @click.outside="datePickerOpen = false"
                        x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                        class="absolute start-0 top-full z-50 mt-2 w-72 rounded-2xl border border-luxury-border bg-luxury-charcoal p-3 shadow-2xl shadow-black/50">
                        <div class="mb-2 flex items-center justify-between">
                            <button type="button" @click="calendarPrevMonth()" aria-label="{{ __('Previous month') }}"
                                class="flex h-7 w-7 items-center justify-center rounded-lg text-luxury-muted transition hover:bg-luxury-graphite hover:text-luxury-white">
                                <x-icon name="chevron-left" class="h-3.5 w-3.5" />
                            </button>
                            <span class="text-sm font-semibold text-luxury-white" x-text="calendarMonthLabel()"></span>
                            <button type="button" @click="calendarNextMonth()" aria-label="{{ __('Next month') }}"
                                class="flex h-7 w-7 items-center justify-center rounded-lg text-luxury-muted transition hover:bg-luxury-graphite hover:text-luxury-white">
                                <x-icon name="chevron-right" class="h-3.5 w-3.5" />
                            </button>
                        </div>
                        <div class="grid grid-cols-7 gap-1 text-center text-[10px] font-medium text-luxury-muted">
                            <template x-for="wd in weekdayLabels()" :key="wd"><span x-text="wd"></span></template>
                        </div>
                        <div class="mt-1 grid grid-cols-7 gap-1">
                            <template x-for="(cell, idx) in calendarDays()" :key="idx">
                                <button type="button" x-show="cell" :disabled="cell?.disabled" @click="cell && selectDate(cell.dateStr)"
                                    class="flex h-8 w-8 items-center justify-center rounded-full text-xs transition"
                                    :class="{
                                        'bg-luxury-gold text-luxury-black font-semibold': cell?.selected,
                                        'text-luxury-gold ring-1 ring-luxury-gold': cell?.today && !cell?.selected,
                                        'text-luxury-white hover:bg-luxury-graphite': cell && !cell.selected && !cell.disabled && !cell.today,
                                        'text-luxury-muted/30': cell?.disabled,
                                    }"
                                    x-text="cell?.day"></button>
                            </template>
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-red-400">{{ $errors->first('return_date') }}</p>
                </div>
                <div class="relative isolate" :class="(timePickerOpen && timePickerField === 'returnTime') ? 'z-30' : 'z-20'">
                    <label class="mb-1 flex items-center gap-1 text-[11px] font-medium text-luxury-muted">
                        <x-icon name="clock" class="h-3 w-3" />
                        {{ __('Return Time') }}
                    </label>
                    <button type="button" @click="openTimePicker('returnTime')"
                        class="flex w-full items-center gap-2 rounded-xl border border-luxury-border bg-luxury-black/40 px-3 py-2.5 text-start transition hover:border-luxury-gold/40 focus:outline-none focus-visible:ring-2 focus-visible:ring-luxury-gold/50">
                        <x-icon name="clock" class="h-3.5 w-3.5 shrink-0 text-luxury-muted" />
                        <span class="flex-1 truncate text-sm text-luxury-white" x-text="formatTimeDisplay('returnTime') || '{{ __('Select time') }}'"></span>
                    </button>
                    <input type="hidden" name="return_time" :value="returnTime">

                    <div x-show="timePickerOpen && timePickerField === 'returnTime'" x-cloak @click.outside="timePickerOpen = false"
                        x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                        class="absolute start-0 top-full z-50 mt-2 max-h-64 w-40 overflow-y-auto rounded-2xl border border-luxury-border bg-luxury-charcoal p-1.5 shadow-2xl shadow-black/50">
                        <template x-for="slot in timeSlots" :key="slot">
                            <button type="button" @click="selectTime(slot)"
                                class="flex w-full items-center rounded-lg px-3 py-2 text-start text-sm transition hover:bg-luxury-graphite"
                                :class="isTimeSelected(slot) ? 'text-luxury-gold font-medium' : 'text-luxury-white'"
                                x-text="formatTimeLabel(slot)"></button>
                        </template>
                    </div>
                    <p class="mt-1 text-xs text-red-400">{{ $errors->first('return_time') }}</p>
                </div>
            </div>
        </div>

        {{-- Vehicle Category / Full Name / Email / Phone — 4 equal columns on wide screens --}}
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
            {{-- Vehicle Category — rich searchable listbox --}}
            <div class="relative isolate" :class="vehicleOpen ? 'z-30' : 'z-20'" @click.outside="vehicleOpen = false">
                <label class="mb-1 flex items-center gap-1 text-[11px] font-medium text-luxury-muted">
                    <x-icon name="car" class="h-3 w-3" />
                    {{ __('Vehicle Category') }}
                </label>
                <button type="button" @click="vehicleOpen = !vehicleOpen" aria-haspopup="listbox" :aria-expanded="vehicleOpen"
                    class="flex w-full items-center gap-2 rounded-xl border border-luxury-border bg-luxury-black/40 px-2 py-1.5 text-start transition hover:border-luxury-gold/40 focus:outline-none focus-visible:ring-2 focus-visible:ring-luxury-gold/50">
                    <template x-if="currentCategory()?.image_url">
                        <img :src="currentCategory().image_url" alt="" class="h-7 w-10 shrink-0 rounded-lg object-cover">
                    </template>
                    <template x-if="!currentCategory()?.image_url">
                        <span class="flex h-7 w-10 shrink-0 items-center justify-center rounded-lg bg-luxury-graphite text-luxury-muted">
                            <x-icon name="car" class="h-3.5 w-3.5" />
                        </span>
                    </template>
                    <span class="min-w-0 flex-1 truncate text-sm font-medium text-luxury-white" x-text="currentCategory()?.name || '{{ __('Any Vehicle') }}'"></span>
                    <x-icon name="chevron-down" class="h-3.5 w-3.5 shrink-0 text-luxury-muted transition-transform" x-bind:class="{ 'rotate-180': vehicleOpen }" />
                </button>
                <input type="hidden" name="vehicle_category_id" :value="vehicleCategory">

                <div x-show="vehicleOpen" x-cloak
                    x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                    role="listbox" class="absolute start-0 end-0 top-full z-50 mt-2 max-h-72 overflow-y-auto rounded-2xl border border-luxury-border bg-luxury-charcoal p-1.5 shadow-2xl shadow-black/50">
                    <template x-for="category in categories" :key="category.id">
                        <button type="button" role="option" :aria-selected="vehicleCategory == category.id" @click="vehicleCategory = category.id; vehicleOpen = false"
                            class="flex w-full items-center gap-2 rounded-xl px-1.5 py-1.5 text-start transition hover:bg-luxury-graphite">
                            <template x-if="category.image_url">
                                <img :src="category.image_url" alt="" class="h-7 w-10 shrink-0 rounded-lg object-cover">
                            </template>
                            <template x-if="!category.image_url">
                                <span class="flex h-7 w-10 shrink-0 items-center justify-center rounded-lg bg-luxury-graphite text-luxury-muted">
                                    <x-icon name="car" class="h-3.5 w-3.5" />
                                </span>
                            </template>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-medium" :class="vehicleCategory == category.id ? 'text-luxury-gold' : 'text-luxury-white'" x-text="category.name"></span>
                            </span>
                            <x-icon name="check-circle" class="h-3.5 w-3.5 shrink-0 text-luxury-gold" x-show="vehicleCategory == category.id" x-cloak />
                        </button>
                    </template>
                </div>
                <p class="mt-1 text-xs text-red-400">{{ $errors->first('vehicle_category_id') }}</p>
            </div>

            @auth('customer')
                {{-- Logged-in customers book under their own account — no need to retype contact details. --}}
                @php $widgetCustomer = auth()->guard('customer')->user(); @endphp
                <div class="flex items-center gap-3 rounded-xl border border-luxury-border bg-luxury-black/40 px-3 py-2.5 sm:col-span-2 lg:col-span-3">
                    <img src="{{ $widgetCustomer->avatar_url }}" alt="{{ $widgetCustomer->name }}"
                        class="h-9 w-9 shrink-0 rounded-lg object-cover" onerror="this.src='https://ui-avatars.com/api/?background=c9a24b&color=0a0a0a&name={{ urlencode($widgetCustomer->name) }}'">
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-luxury-white">{{ __('Booking as :name', ['name' => $widgetCustomer->name]) }}</p>
                        <p class="truncate text-xs text-luxury-muted">{{ $widgetCustomer->email }}{{ $widgetCustomer->phone ? ' · '.$widgetCustomer->phone : '' }}</p>
                    </div>
                    <x-icon name="check-circle" class="h-4 w-4 shrink-0 text-luxury-gold" />
                </div>
            @else
                {{-- Full Name --}}
                <div>
                    <label for="name" class="mb-1 flex items-center gap-1 text-[11px] font-medium text-luxury-muted">
                        <x-icon name="user" class="h-3 w-3" />
                        {{ __('Full Name') }}
                    </label>
                    <input type="text" id="name" name="name" x-model="name" required placeholder="{{ __('Full Name') }}"
                        class="w-full rounded-xl border border-luxury-border bg-luxury-black/40 px-3 py-2.5 text-sm text-luxury-white placeholder:text-luxury-muted transition focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                    <p class="mt-1 text-xs text-red-400">{{ $errors->first('name') }}</p>
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="mb-1 flex items-center gap-1 text-[11px] font-medium text-luxury-muted">
                        <x-icon name="mail" class="h-3 w-3" />
                        {{ __('Email') }} <span class="text-luxury-muted/60">({{ __('optional') }})</span>
                    </label>
                    <input type="email" id="email" name="email" x-model="email" placeholder="{{ __('Email') }}"
                        class="w-full rounded-xl border border-luxury-border bg-luxury-black/40 px-3 py-2.5 text-sm text-luxury-white placeholder:text-luxury-muted transition focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                    <p class="mt-1 text-xs text-red-400">{{ $errors->first('email') }}</p>
                </div>

                {{-- Phone: country code + number, one unified box --}}
                <div>
                    <label class="mb-1 flex items-center gap-1 text-[11px] font-medium text-luxury-muted">
                        <x-icon name="phone" class="h-3 w-3" />
                        {{ __('Phone') }}
                    </label>
                    <div class="relative isolate flex items-stretch rounded-xl border border-luxury-border bg-luxury-black/40 transition focus-within:border-luxury-gold focus-within:ring-1 focus-within:ring-luxury-gold" :class="phoneCodeOpen ? 'z-30' : 'z-20'" @click.outside="phoneCodeOpen = false; dialSearch = ''">
                        <button type="button" @click="phoneCodeOpen = !phoneCodeOpen; dialSearch = ''; $nextTick(() => $refs.dialSearchInput?.focus())" aria-haspopup="listbox" :aria-expanded="phoneCodeOpen"
                            class="flex shrink-0 items-center gap-1.5 rounded-s-xl border-e border-luxury-border/60 px-2 py-2.5 text-sm text-luxury-white transition hover:bg-luxury-graphite/40 focus:outline-none focus-visible:ring-2 focus-visible:ring-luxury-gold/50">
                            <span class="fi" :class="'fi-' + dialCountry().iso2"></span>
                            <span x-text="dialCode"></span>
                            <x-icon name="chevron-down" class="h-3 w-3 text-luxury-muted transition-transform" x-bind:class="{ 'rotate-180': phoneCodeOpen }" />
                        </button>
                        <input type="tel" id="phone_number" x-model="phoneNumber" placeholder="{{ __('Phone Number') }}"
                            class="min-w-0 flex-1 rounded-e-xl bg-transparent px-3 py-2.5 text-sm text-luxury-white placeholder:text-luxury-muted focus:outline-none">

                        {{-- Panel anchors to the whole merged box (not just the code button) so it
                             can never be wider than its own field column — no more page overflow. --}}
                        <div x-show="phoneCodeOpen" x-cloak
                            x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                            role="listbox" class="absolute start-0 end-0 top-full z-50 mt-2 overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal shadow-2xl shadow-black/50">
                            <div class="border-b border-luxury-border/60 p-2">
                                <input type="text" x-ref="dialSearchInput" x-model="dialSearch" placeholder="{{ __('Search country or code') }}"
                                    class="w-full rounded-lg border border-luxury-border bg-luxury-black/40 px-3 py-1.5 text-xs text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                            </div>
                            <div class="max-h-56 overflow-y-auto p-1.5">
                                <template x-for="country in filteredDialCodes()" :key="country.iso2 + country.dial">
                                    <button type="button" role="option" @click="dialCode = country.dial; phoneCodeOpen = false; dialSearch = ''"
                                        class="flex w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-start text-sm transition hover:bg-luxury-graphite"
                                        :class="dialCode === country.dial ? 'text-luxury-gold' : 'text-luxury-muted'">
                                        <span class="fi" :class="'fi-' + country.iso2"></span>
                                        <span class="min-w-0 flex-1 truncate" x-text="country.name"></span>
                                        <span x-text="country.dial"></span>
                                    </button>
                                </template>
                                <p x-show="filteredDialCodes().length === 0" class="px-3 py-4 text-center text-xs text-luxury-muted">{{ __('No countries found.') }}</p>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="phone" :value="phoneNumber ? (dialCode + ' ' + phoneNumber) : ''">
                </div>
            @endauth
        </div>

        {{-- Hidden fields populated by Google Places / the live quote --}}
        <input type="hidden" name="pickup_lat" :value="pickupLat ?? ''">
        <input type="hidden" name="pickup_lng" :value="pickupLng ?? ''">
        <input type="hidden" name="pickup_place_id" :value="pickupPlaceId ?? ''">
        <input type="hidden" name="dropoff_lat" :value="dropoffLat ?? ''">
        <input type="hidden" name="dropoff_lng" :value="dropoffLng ?? ''">
        <input type="hidden" name="dropoff_place_id" :value="dropoffPlaceId ?? ''">
        <input type="hidden" name="distance_km" :value="distanceKm ?? ''">
        <input type="hidden" name="return_pickup_lat" :value="returnPickupLat ?? ''">
        <input type="hidden" name="return_pickup_lng" :value="returnPickupLng ?? ''">
        <input type="hidden" name="return_pickup_place_id" :value="returnPickupPlaceId ?? ''">
        <input type="hidden" name="return_dropoff_lat" :value="returnDropoffLat ?? ''">
        <input type="hidden" name="return_dropoff_lng" :value="returnDropoffLng ?? ''">
        <input type="hidden" name="return_dropoff_place_id" :value="returnDropoffPlaceId ?? ''">
        <input type="hidden" name="return_distance_km" :value="returnDistanceKm ?? ''">

        {{-- Live Quote --}}
        <div x-show="calculating" x-cloak x-transition class="rounded-xl border border-luxury-border bg-luxury-black/40 px-4 py-3 text-center text-sm text-luxury-muted">
            <span class="inline-flex items-center gap-2">
                <svg class="h-4 w-4 animate-spin text-luxury-gold" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                {{ __('Calculating...') }}
            </span>
        </div>

        <div x-show="quoteError" x-cloak x-transition class="rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-center text-sm text-red-400" x-text="quoteError"></div>

        <div x-show="quote && !calculating" x-cloak x-transition class="space-y-3 rounded-2xl border border-luxury-border bg-luxury-black/40 p-4">
            <div class="flex items-center justify-between gap-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-luxury-muted">{{ __('Estimated Fare') }}</p>
                <p x-show="availableDriversCount > 0" x-cloak class="flex items-center gap-1.5 text-xs text-emerald-400">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                    <span x-text="availableDriversCount + ' {{ __('driver(s) available for your booking') }}'"></span>
                </p>
            </div>

            <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm sm:grid-cols-3">
                <div>
                    <dt class="text-xs text-luxury-muted">{{ __('Distance') }}</dt>
                    <dd class="text-luxury-white" x-text="totalDistanceLabel()"></dd>
                </div>
                <div>
                    <dt class="text-xs text-luxury-muted">{{ __('Duration') }}</dt>
                    <dd class="text-luxury-white" x-text="totalDurationLabel()"></dd>
                </div>
                <div>
                    <dt class="text-xs text-luxury-muted">{{ __('Vehicle') }}</dt>
                    <dd class="truncate text-luxury-white" x-text="vehicleName || '—'"></dd>
                </div>
                <div x-show="quote && quote.base_fare > 0">
                    <dt class="text-xs text-luxury-muted">{{ __('Base Fare') }}</dt>
                    <dd class="text-luxury-white" x-text="quote ? money(quote.base_fare) : ''"></dd>
                </div>
                <div x-show="quote && quote.distance_fare > 0">
                    <dt class="text-xs text-luxury-muted">{{ __('Distance Cost') }}</dt>
                    <dd class="text-luxury-white" x-text="quote ? money(quote.distance_fare) : ''"></dd>
                </div>
                <div x-show="quote && quote.hour_fare > 0">
                    <dt class="text-xs text-luxury-muted">{{ __('Hourly Cost') }}</dt>
                    <dd class="text-luxury-white" x-text="quote ? money(quote.hour_fare) : ''"></dd>
                </div>
                <template x-for="item in extraChargeItems()" :key="item.label">
                    <div>
                        <dt class="text-xs text-luxury-muted" x-text="item.label"></dt>
                        <dd class="text-luxury-white" x-text="money(item.value)"></dd>
                    </div>
                </template>
                <div x-show="appliedCoupon">
                    <dt class="text-xs text-luxury-muted">{{ __('Discount') }}</dt>
                    <dd class="text-emerald-400" x-text="appliedCoupon ? '&minus;' + money(appliedCoupon.discount) : ''"></dd>
                </div>
            </dl>

            {{-- Coupon code --}}
            <div class="border-t border-luxury-border/60 pt-3">
                <template x-if="!appliedCoupon">
                    <div class="flex items-center gap-2">
                        <input type="text" x-model="couponCode" @keydown.enter.prevent="applyCoupon()"
                            placeholder="{{ __('Coupon code') }}"
                            class="min-w-0 flex-1 rounded-lg border border-luxury-border bg-luxury-black/40 px-3 py-2 text-sm uppercase text-luxury-white placeholder:text-luxury-muted placeholder:normal-case focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                        <button type="button" @click="applyCoupon()" :disabled="couponChecking || !couponCode.trim()"
                            class="shrink-0 rounded-lg border border-luxury-gold/40 px-4 py-2 text-xs font-semibold text-luxury-gold transition hover:bg-luxury-gold/10 disabled:cursor-not-allowed disabled:opacity-50">
                            <span x-show="!couponChecking">{{ __('Apply') }}</span>
                            <span x-show="couponChecking" x-cloak>{{ __('Checking...') }}</span>
                        </button>
                    </div>
                </template>

                {{-- Suggested codes — the only place on the site a visitor can
                     discover a coupon exists at all; clicking one still goes
                     through the same applyCoupon() check as typing it. --}}
                <template x-if="!appliedCoupon && availableCoupons.length">
                    <div class="mt-2 flex flex-wrap items-center gap-1.5">
                        <span class="text-[11px] text-luxury-muted">{{ __('Offers:') }}</span>
                        <template x-for="coupon in availableCoupons" :key="coupon.code">
                            <button type="button" @click="couponCode = coupon.code; applyCoupon()"
                                class="rounded-full border border-luxury-gold/30 bg-luxury-gold/5 px-2.5 py-1 text-[11px] font-medium text-luxury-gold transition hover:bg-luxury-gold/15">
                                <span x-text="coupon.code"></span> <span class="text-luxury-muted" x-text="'· ' + coupon.label"></span>
                            </button>
                        </template>
                    </div>
                </template>
                <template x-if="appliedCoupon">
                    <div class="flex items-center justify-between gap-3 rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-3 py-2">
                        <span class="flex items-center gap-1.5 text-xs font-medium text-emerald-400">
                            <x-icon name="check-circle" class="h-3.5 w-3.5 shrink-0" />
                            <span x-text="appliedCoupon.code + ' — ' + appliedCoupon.label"></span>
                        </span>
                        <button type="button" @click="removeCoupon()" class="shrink-0 text-xs font-medium text-luxury-muted underline-offset-2 hover:text-luxury-white hover:underline">
                            {{ __('Remove') }}
                        </button>
                    </div>
                </template>
                <p x-show="couponError" x-cloak class="mt-1.5 text-xs text-red-400" x-text="couponError"></p>
                <input type="hidden" name="coupon_code" :value="appliedCoupon ? appliedCoupon.code : ''">
            </div>

            <div class="border-t border-luxury-border/60 pt-3 text-center">
                <p class="text-xs uppercase tracking-wide text-luxury-muted">{{ __('Estimated Total') }}</p>
                <p x-show="appliedCoupon" x-cloak class="text-sm text-luxury-muted line-through decoration-red-500 decoration-2" x-text="quote ? money(quote.total) : ''"></p>
                <p class="text-3xl font-bold text-luxury-gold" x-text="quote ? money(finalTotal()) : ''"></p>
            </div>

            <p class="text-center text-[11px] leading-snug text-luxury-muted">
                {{ __('Final fare may vary depending on traffic, waiting time, tolls and additional services.') }}
            </p>

            <div x-show="drivers.length > 0" x-cloak class="space-y-2 border-t border-luxury-border/60 pt-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-luxury-muted">{{ __('Drivers for This Vehicle') }}</p>
                <template x-for="driver in drivers" :key="driver.name + driver.status">
                    <div class="flex items-center justify-between gap-3 rounded-lg bg-luxury-black/30 px-3 py-2 text-xs">
                        <span class="flex items-center gap-2 text-luxury-white">
                            <span class="h-1.5 w-1.5 shrink-0 rounded-full"
                                :class="{
                                    'bg-emerald-400': driver.status === 'available',
                                    'bg-blue-400': driver.status === 'busy',
                                    'bg-luxury-muted': driver.status === 'offline',
                                }"></span>
                            <span x-text="driver.name"></span>
                        </span>
                        <span class="text-luxury-muted" x-text="driverStatusText(driver)"></span>
                    </div>
                </template>
            </div>
        </div>

        {{-- Passengers / Luggage (left) + Book Now (right) --}}
        <div class="flex flex-col gap-3 pt-1 sm:flex-row sm:items-end sm:justify-between">
            <div class="grid grid-cols-2 gap-2 sm:w-52">
                <div>
                    <label class="mb-1 flex items-center gap-1 text-[11px] font-medium text-luxury-muted">
                        <x-icon name="users" class="h-3 w-3" />
                        {{ __('Passengers') }}
                    </label>
                    <div class="flex items-center justify-between rounded-xl border border-luxury-border bg-luxury-black/40 px-1.5 py-1">
                        <button type="button" @click="incrementPassengers(-1)" aria-label="{{ __('Decrease passengers') }}"
                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-luxury-muted transition hover:bg-luxury-graphite hover:text-luxury-white active:scale-90">&minus;</button>
                        <span class="text-sm font-medium text-luxury-white" x-text="passengers"></span>
                        <input type="hidden" name="passengers" :value="passengers">
                        <button type="button" @click="incrementPassengers(1)" aria-label="{{ __('Increase passengers') }}"
                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-luxury-muted transition hover:bg-luxury-graphite hover:text-luxury-white active:scale-90">+</button>
                    </div>
                </div>

                <div>
                    <label class="mb-1 flex items-center gap-1 text-[11px] font-medium text-luxury-muted">
                        <x-icon name="briefcase" class="h-3 w-3" />
                        {{ __('Luggage') }}
                    </label>
                    <div class="flex items-center justify-between rounded-xl border border-luxury-border bg-luxury-black/40 px-1.5 py-1">
                        <button type="button" @click="incrementLuggage(-1)" aria-label="{{ __('Decrease luggage') }}"
                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-luxury-muted transition hover:bg-luxury-graphite hover:text-luxury-white active:scale-90">&minus;</button>
                        <span class="text-sm font-medium text-luxury-white" x-text="luggage"></span>
                        <input type="hidden" name="luggage" :value="luggage">
                        <button type="button" @click="incrementLuggage(1)" aria-label="{{ __('Increase luggage') }}"
                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-luxury-muted transition hover:bg-luxury-graphite hover:text-luxury-white active:scale-90">+</button>
                    </div>
                </div>
            </div>

            <button type="submit" :disabled="submitting"
                class="flex w-full items-center justify-center gap-2 rounded-xl bg-luxury-gold px-6 py-3.5 text-sm font-semibold text-luxury-black transition hover:bg-luxury-gold-light active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto sm:min-w-[12rem]">
                <template x-if="!submitting">
                    <span class="flex items-center gap-2">
                        <x-icon name="search" class="h-3.5 w-3.5" />
                        {{ __('Book Now') }}
                    </span>
                </template>
                <template x-if="submitting">
                    <span class="flex items-center gap-2">
                        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        {{ __('Book Now') }}
                    </span>
                </template>
            </button>
        </div>
    </form>
</div>

<script>
    function bookingSearchBox(config) {
        return {
            pickup: config.initial.pickup || '',
            dropoff: config.initial.dropoff || '',
            date: config.initial.date || '',
            time: config.initial.time || '',
            passengers: config.initial.passengers || 1,
            luggage: config.initial.luggage || 0,
            vehicleCategory: config.initial.vehicleCategory || (config.categories[0]?.id ?? ''),
            categories: config.categories,
            serviceTypes: config.serviceTypes,
            availableCoupons: config.availableCoupons,
            dialCodes: config.dialCodes,
            type: config.initial.type || 'one_way',
            hours: config.initial.hours || 2,
            returnDate: config.initial.returnDate || '',
            returnTime: config.initial.returnTime || '',
            returnPickup: config.initial.returnPickup || '',
            returnDropoff: config.initial.returnDropoff || '',
            returnStops: config.initial.returnStops.length ? config.initial.returnStops : [],
            stops: config.initial.stops.length ? config.initial.stops : [],
            name: config.initial.name || '',
            email: config.initial.email || '',
            phone: config.initial.phone || '',
            dialCode: config.defaultDialCode || '+1',
            dialSearch: '',
            phoneNumber: config.initial.phone || '',
            today: config.defaultDate,

            // UI state for the custom dropdowns.
            tripTypeOpen: false,
            vehicleOpen: false,
            phoneCodeOpen: false,
            submitting: false,
            autocompleteReady: false,

            // Custom date/time pickers (replace native <input type="date/time">
            // so the whole field is clickable and the popup matches the theme
            // instead of the browser's default OS picker).
            datePickerOpen: false,
            datePickerField: null,
            calendarViewMonth: '',
            timePickerOpen: false,
            timePickerField: null,
            timeSlots: [],

            // Populated by Google Places Autocomplete (see initAutocomplete()).
            pickupLat: null,
            pickupLng: null,
            pickupPlaceId: null,
            dropoffLat: null,
            dropoffLng: null,
            dropoffPlaceId: null,
            returnPickupLat: null,
            returnPickupLng: null,
            returnPickupPlaceId: null,
            returnDropoffLat: null,
            returnDropoffLng: null,
            returnDropoffPlaceId: null,

            // Live quote state.
            distanceKm: null,
            durationMinutes: null,
            returnDistanceKm: null,
            returnDurationMinutes: null,
            vehicleName: null,
            availableDriversCount: null,
            drivers: [],
            quote: null,
            calculating: false,
            quoteError: null,
            quoteDebounce: null,
            quoteSignature: null,
            quoteRequestId: 0,

            // Coupon state — separate from the quote fetch above so typing a
            // code never triggers the distance/pricing round-trip, and vice
            // versa. appliedCoupon is only ever a courtesy preview; the
            // actual discount is re-checked server-side at submit time.
            couponCode: '',
            appliedCoupon: null,
            couponError: null,
            couponChecking: false,

            init() {
                for (let h = 0; h < 24; h++) {
                    for (let m = 0; m < 60; m += 30) {
                        this.timeSlots.push(String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0'));
                    }
                }

                // Default pickup date/time to "right now" in the business's
                // own configured timezone (rounded up to the nearest
                // half-hour slot, computed server-side) rather than leaving
                // them blank — only when nothing was already prefilled via
                // old()/query string, e.g. a validation-error redisplay or a
                // "rebook" link.
                if (!this.date) {
                    this.date = config.defaultDate;
                }

                if (!this.time) {
                    this.time = config.defaultTime;
                }

                this.$watch('pickup', () => this.scheduleQuote());
                this.$watch('dropoff', () => this.scheduleQuote());
                this.$watch('pickupLat', () => this.scheduleQuote());
                this.$watch('dropoffLat', () => this.scheduleQuote());
                this.$watch('returnPickupLat', () => this.scheduleQuote());
                this.$watch('returnDropoffLat', () => this.scheduleQuote());
                this.$watch('stops', () => this.scheduleQuote());
                this.$watch('returnStops', () => this.scheduleQuote());
                this.$watch('vehicleCategory', () => this.scheduleQuote());
                this.$watch('type', () => this.scheduleQuote());
                this.$watch('hours', () => this.scheduleQuote());
                this.$watch('passengers', () => this.scheduleQuote());
                this.$watch('date', () => this.scheduleQuote());
                this.$watch('time', () => this.scheduleQuote());

                // The Google Maps script may already be loaded (e.g. back-forward
                // cache) by the time this component initializes.
                if (window.google?.maps?.places) {
                    this.initAutocomplete();
                }
            },

            initAutocomplete() {
                if (!window.google?.maps?.places || this.autocompleteReady) return;
                this.autocompleteReady = true;

                const bind = (inputRef, onPlace) => {
                    const el = this.$refs[inputRef];
                    if (!el) return;
                    const autocomplete = new google.maps.places.Autocomplete(el, { fields: ['place_id', 'geometry', 'formatted_address'] });
                    autocomplete.addListener('place_changed', () => onPlace(autocomplete.getPlace()));
                };

                bind('pickupInput', (place) => {
                    if (!place.geometry) return;
                    this.pickup = place.formatted_address || this.pickup;
                    this.pickupLat = place.geometry.location.lat();
                    this.pickupLng = place.geometry.location.lng();
                    this.pickupPlaceId = place.place_id || null;
                });

                bind('dropoffInput', (place) => {
                    if (!place.geometry) return;
                    this.dropoff = place.formatted_address || this.dropoff;
                    this.dropoffLat = place.geometry.location.lat();
                    this.dropoffLng = place.geometry.location.lng();
                    this.dropoffPlaceId = place.place_id || null;
                });

                bind('returnPickupInput', (place) => {
                    if (!place.geometry) return;
                    this.returnPickup = place.formatted_address || this.returnPickup;
                    this.returnPickupLat = place.geometry.location.lat();
                    this.returnPickupLng = place.geometry.location.lng();
                    this.returnPickupPlaceId = place.place_id || null;
                });

                bind('returnDropoffInput', (place) => {
                    if (!place.geometry) return;
                    this.returnDropoff = place.formatted_address || this.returnDropoff;
                    this.returnDropoffLat = place.geometry.location.lat();
                    this.returnDropoffLng = place.geometry.location.lng();
                    this.returnDropoffPlaceId = place.place_id || null;
                });
            },

            // Stop rows are created dynamically (x-for), so each one binds its
            // own Autocomplete instance here (via x-init) rather than through
            // the static $refs used for the fixed pickup/dropoff fields above.
            // Without this, stops were free-typed text with no coordinates —
            // meaning the distance/quote silently ignored them entirely.
            initStopAutocomplete(el, index, isReturn) {
                if (!window.google?.maps?.places) return;

                const autocomplete = new google.maps.places.Autocomplete(el, { fields: ['place_id', 'geometry', 'formatted_address'] });
                autocomplete.addListener('place_changed', () => {
                    const place = autocomplete.getPlace();
                    if (!place.geometry) return;

                    const list = isReturn ? this.returnStops : this.stops;
                    const stop = list[index];
                    if (!stop) return;

                    stop.location = place.formatted_address || stop.location;
                    stop.lat = place.geometry.location.lat();
                    stop.lng = place.geometry.location.lng();
                    stop.placeId = place.place_id || null;
                });
            },

            typeLabel() {
                return this.serviceTypes.find((option) => option.value === this.type)?.label || '';
            },

            // Custom date picker — datePickerField is either 'date' or 'returnDate',
            // pointing at whichever underlying model the open calendar edits.
            openDatePicker(field) {
                this.datePickerField = field;
                this.calendarViewMonth = (this[field] || this.today).slice(0, 7);
                this.datePickerOpen = true;
            },

            calendarMonthLabel() {
                if (!this.calendarViewMonth) return '';
                const [y, m] = this.calendarViewMonth.split('-').map(Number);

                return new Date(y, m - 1, 1).toLocaleDateString(undefined, { month: 'long', year: 'numeric' });
            },

            weekdayLabels() {
                const labels = [];
                const base = new Date(2023, 0, 1); // a Sunday

                for (let i = 0; i < 7; i++) {
                    const d = new Date(base);
                    d.setDate(base.getDate() + i);
                    labels.push(d.toLocaleDateString(undefined, { weekday: 'narrow' }));
                }

                return labels;
            },

            calendarPrevMonth() {
                let [y, m] = this.calendarViewMonth.split('-').map(Number);
                m--;
                if (m < 1) { m = 12; y--; }
                this.calendarViewMonth = y + '-' + String(m).padStart(2, '0');
            },

            calendarNextMonth() {
                let [y, m] = this.calendarViewMonth.split('-').map(Number);
                m++;
                if (m > 12) { m = 1; y++; }
                this.calendarViewMonth = y + '-' + String(m).padStart(2, '0');
            },

            calendarDays() {
                if (!this.calendarViewMonth) return [];
                const [y, m] = this.calendarViewMonth.split('-').map(Number);
                const firstWeekday = new Date(y, m - 1, 1).getDay();
                const daysInMonth = new Date(y, m, 0).getDate();
                const cells = [];

                for (let i = 0; i < firstWeekday; i++) cells.push(null);

                for (let d = 1; d <= daysInMonth; d++) {
                    const dateStr = y + '-' + String(m).padStart(2, '0') + '-' + String(d).padStart(2, '0');
                    cells.push({
                        day: d,
                        dateStr,
                        disabled: this.isDateDisabled(dateStr),
                        selected: this[this.datePickerField] === dateStr,
                        today: dateStr === this.today,
                    });
                }

                return cells;
            },

            isDateDisabled(dateStr) {
                const min = this.datePickerField === 'returnDate' ? (this.date || this.today) : this.today;

                return dateStr < min;
            },

            selectDate(dateStr) {
                if (this.isDateDisabled(dateStr)) return;
                this[this.datePickerField] = dateStr;
                this.datePickerOpen = false;
            },

            formatDateDisplay(field) {
                const val = this[field];
                if (!val) return '';
                const [y, m, d] = val.split('-').map(Number);

                return new Date(y, m - 1, d).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
            },

            // Custom time picker — same open/select pattern as the date picker above.
            openTimePicker(field) {
                this.timePickerField = field;
                this.timePickerOpen = true;
            },

            selectTime(slot) {
                this[this.timePickerField] = slot;
                this.timePickerOpen = false;
            },

            isTimeSelected(slot) {
                return this[this.timePickerField] === slot;
            },

            formatTimeLabel(slot) {
                const [h, m] = slot.split(':').map(Number);

                // `hour12` is forced explicitly rather than left to the
                // visitor's browser locale — several common locales (e.g.
                // Dutch/French, both relevant here) default to 24-hour time,
                // which silently dropped AM/PM depending on who was looking.
                return new Date(2000, 0, 1, h, m).toLocaleTimeString(undefined, { hour: 'numeric', minute: '2-digit', hour12: true });
            },

            formatTimeDisplay(field) {
                return this[field] ? this.formatTimeLabel(this[field]) : '';
            },

            currentCategory() {
                return this.categories.find((category) => category.id == this.vehicleCategory) || null;
            },

            dialCountry() {
                return this.dialCodes.find((country) => country.dial === this.dialCode) || this.dialCodes[0];
            },

            filteredDialCodes() {
                const search = this.dialSearch.trim().toLowerCase();
                if (!search) return this.dialCodes;

                return this.dialCodes.filter((country) => country.name.toLowerCase().includes(search) || country.dial.includes(search));
            },

            incrementPassengers(delta) {
                this.passengers = Math.min(20, Math.max(1, this.passengers + delta));
            },
            incrementLuggage(delta) {
                this.luggage = Math.min(20, Math.max(0, this.luggage + delta));
            },

            notify(type, message) {
                window.dispatchEvent(new CustomEvent('notify', { detail: { type, message } }));
            },

            hasRequiredFields() {
                const hasDestination = this.type === 'hourly' || this.dropoff;
                const isRoundTrip = this.type === 'round_trip';
                const hasReturnDetails = !isRoundTrip || (this.returnDate && this.returnTime && this.returnPickup && this.returnDropoff);

                return this.pickup && hasDestination && this.date && this.time && hasReturnDetails;
            },

            startVoiceSearch() {
                this.notify('success', @json(__('Voice search is coming soon.')));
            },

            handleSubmit(event) {
                if (!this.hasRequiredFields()) {
                    event.preventDefault();
                    this.notify('error', @json(__('Please fill in pickup, drop-off, date and time.')));
                    return;
                }

                // Opened synchronously inside this submit handler (the same
                // user gesture that clicked "Book Now"), so browsers don't
                // treat it as an unsolicited popup — the booking itself is
                // still created normally via this form's own POST below.
                this.notifyWhatsApp();

                this.submitting = true;
            },

            notifyWhatsApp() {
                if (!config.whatsappNumber) return;

                const lines = [
                    @json(__('New booking request')) + '!',
                    '',
                    @json(__('Name')) + ': ' + (this.name || '-'),
                    @json(__('Phone')) + ': ' + (this.phone || '-'),
                    @json(__('Pickup')) + ': ' + (this.pickup || '-'),
                ];

                if (this.type !== 'hourly') {
                    lines.push(@json(__('Drop-off')) + ': ' + (this.dropoff || '-'));
                } else {
                    lines.push(@json(__('Duration')) + ': ' + this.hours + ' ' + @json(__('hour(s)')));
                }

                lines.push(
                    @json(__('Date')) + ': ' + (this.date || '-'),
                    @json(__('Time')) + ': ' + (this.time || '-'),
                    @json(__('Vehicle')) + ': ' + (this.vehicleName || '-'),
                );

                if (this.quote) {
                    lines.push(@json(__('Estimated Fare')) + ': ' + this.money(this.finalTotal()));

                    if (this.appliedCoupon) {
                        lines.push(@json(__('Coupon Applied')) + ': ' + this.appliedCoupon.code);
                    }
                }

                const digits = config.whatsappNumber.replace(/\D/g, '');
                const url = 'https://wa.me/' + digits + '?text=' + encodeURIComponent(lines.join('\n'));

                window.open(url, '_blank');
            },

            canQuote() {
                if (!this.vehicleCategory || !this.type) return false;
                if (this.type === 'hourly') return !!this.hours;

                return !!(this.pickupLat && this.pickupLng && this.dropoffLat && this.dropoffLng);
            },

            money(value) {
                return config.currencySymbol + (Number(value) * config.currencyRate).toFixed(2);
            },

            totalDistanceLabel() {
                if (!this.distanceKm) return '—';
                const total = this.type === 'round_trip'
                    ? (this.distanceKm + (this.returnDistanceKm ?? this.distanceKm))
                    : this.distanceKm;

                return Number(total.toFixed(2)) + ' km';
            },

            totalDurationLabel() {
                if (!this.durationMinutes) return '—';
                const total = this.type === 'round_trip'
                    ? (this.durationMinutes + (this.returnDurationMinutes ?? this.durationMinutes))
                    : this.durationMinutes;

                return total + ' min';
            },

            driverStatusText(driver) {
                if (driver.status === 'offline') return @json(__('Offline'));

                const distancePart = driver.distance_km !== null
                    ? (driver.distance_km + ' km · ~' + driver.duration_minutes + ' ' + @json(__('min away')))
                    : null;

                if (driver.status === 'busy') {
                    const freePart = driver.overdue
                        ? @json(__('wrapping up ride'))
                        : (driver.free_in_minutes !== null
                            ? (@json(__('free in')) + ' ' + driver.free_in_minutes + ' ' + @json(__('min')))
                            : @json(__('busy')));

                    return distancePart ? (distancePart + ' · ' + freePart) : freePart;
                }

                return distancePart || @json(__('Available'));
            },

            extraChargeItems() {
                if (!this.quote) return [];

                const labels = {
                    waiting_charge: @json(__('Waiting Charge')),
                    night_charge: @json(__('Night Surcharge')),
                    weekend_charge: @json(__('Weekend Surcharge')),
                    toll_charge: @json(__('Toll Charge')),
                    airport_surcharge: @json(__('Airport Surcharge')),
                    service_fee: @json(__('Service Fee')),
                    extra_passenger_charge: @json(__('Extra Passenger Charge')),
                    approach_charge: @json(__('Pickup Distance Charge')),
                };

                return Object.keys(labels)
                    .filter((key) => (this.quote[key] || 0) > 0)
                    .map((key) => ({ label: labels[key], value: this.quote[key] }));
            },

            // The number actually shown as "Estimated Total" and the one
            // charged at booking time both derive the discount the same
            // way: quote.total minus whatever the last successful coupon
            // check returned — never a client-side recomputation of the
            // coupon's own rules.
            finalTotal() {
                if (!this.quote) return 0;

                return this.appliedCoupon ? this.appliedCoupon.new_total : this.quote.total;
            },

            applyCoupon(silent = false, isRetry = false) {
                const code = (silent ? this.appliedCoupon?.code : this.couponCode).trim();

                if (!code || !this.quote) return;

                this.couponChecking = true;
                if (!silent) this.couponError = null;

                // Deliberately not this.$el.querySelector('input[name="_token"]')
                // — Alpine's $el inside a method reflects whichever element the
                // *calling* directive is bound to, not this component's root.
                // Called from a deeply-nested button (e.g. a coupon chip), that
                // resolves to the button itself, which has no _token descendant
                // — silently sending the literal string "undefined" as the
                // header and failing every request with a CSRF mismatch. The
                // page-level meta tag has no such ambiguity.
                const token = document.querySelector('meta[name="csrf-token"]')?.content;

                fetch(@json(route('coupon.apply')), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token,
                    },
                    body: JSON.stringify({ code, amount: this.quote.total }),
                })
                    .then(async (response) => {
                        // A 419 here is always a stale/mismatched session
                        // token, never the coupon's own fault — one silent
                        // retry (same code, freshly re-read token) clears it
                        // up rather than showing the customer a confusing
                        // "CSRF" error for what looks to them like typing a
                        // code wrong. Left mid-"Checking..." rather than
                        // toggled off, since a second request is already on
                        // its way.
                        if (response.status === 419 && !isRetry) {
                            this.applyCoupon(silent, true);
                            return;
                        }

                        const json = await response.json().catch(() => null);

                        if (!response.ok || !json?.valid) {
                            this.appliedCoupon = null;
                            this.couponError = json?.message || @json(__('That coupon could not be applied.'));
                        } else {
                            this.appliedCoupon = json;
                            this.couponCode = json.code;
                            this.couponError = null;
                        }

                        this.couponChecking = false;
                    })
                    .catch(() => {
                        this.appliedCoupon = null;
                        this.couponError = @json(__('Unable to check that coupon right now.'));
                        this.couponChecking = false;
                    });
            },

            removeCoupon() {
                this.appliedCoupon = null;
                this.couponCode = '';
                this.couponError = null;
            },

            scheduleQuote() {
                clearTimeout(this.quoteDebounce);

                if (!this.canQuote()) {
                    this.quote = null;
                    this.quoteError = null;
                    this.calculating = false;
                    return;
                }

                this.quoteDebounce = setTimeout(() => this.fetchQuote(), 500);
            },

            fetchQuote() {
                const payload = {
                    vehicle_category_id: this.vehicleCategory,
                    type: this.type,
                    pickup_location: this.pickup,
                    pickup_lat: this.pickupLat,
                    pickup_lng: this.pickupLng,
                    pickup_place_id: this.pickupPlaceId,
                    dropoff_location: this.dropoff,
                    dropoff_lat: this.dropoffLat,
                    dropoff_lng: this.dropoffLng,
                    dropoff_place_id: this.dropoffPlaceId,
                    stops: this.stops.filter((stop) => stop.lat && stop.lng).map((stop) => ({ location: stop.location, lat: stop.lat, lng: stop.lng })),
                    return_pickup_location: this.type === 'round_trip' ? this.returnPickup : null,
                    return_pickup_lat: this.type === 'round_trip' ? this.returnPickupLat : null,
                    return_pickup_lng: this.type === 'round_trip' ? this.returnPickupLng : null,
                    return_dropoff_location: this.type === 'round_trip' ? this.returnDropoff : null,
                    return_dropoff_lat: this.type === 'round_trip' ? this.returnDropoffLat : null,
                    return_dropoff_lng: this.type === 'round_trip' ? this.returnDropoffLng : null,
                    return_stops: this.type === 'round_trip'
                        ? this.returnStops.filter((stop) => stop.lat && stop.lng).map((stop) => ({ location: stop.location, lat: stop.lat, lng: stop.lng }))
                        : [],
                    hours: this.type === 'hourly' ? this.hours : null,
                    passengers: this.passengers,
                    pickup_date: this.date,
                    pickup_time: this.time,
                };

                const signature = JSON.stringify(payload);
                if (signature === this.quoteSignature && (this.quote || this.calculating)) return;
                this.quoteSignature = signature;

                const requestId = ++this.quoteRequestId;
                this.calculating = true;
                this.quoteError = null;

                // Deliberately not this.$el.querySelector('input[name="_token"]')
                // — Alpine's $el inside a method reflects whichever element the
                // *calling* directive is bound to, not this component's root.
                // Called from a deeply-nested button (e.g. a coupon chip), that
                // resolves to the button itself, which has no _token descendant
                // — silently sending the literal string "undefined" as the
                // header and failing every request with a CSRF mismatch. The
                // page-level meta tag has no such ambiguity.
                const token = document.querySelector('meta[name="csrf-token"]')?.content;

                fetch(config.quoteUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token,
                    },
                    body: JSON.stringify(payload),
                })
                    .then(async (response) => {
                        const json = await response.json().catch(() => null);
                        if (requestId !== this.quoteRequestId) return; // a newer request has since superseded this one

                        if (!response.ok) {
                            this.quote = null;
                            this.drivers = [];
                            this.quoteError = json?.message || @json(__('Unable to calculate distance.'));
                            return;
                        }

                        this.distanceKm = json.distance_km;
                        this.durationMinutes = json.duration_minutes;
                        this.returnDistanceKm = json.return_distance_km;
                        this.returnDurationMinutes = json.return_duration_minutes;
                        this.vehicleName = json.vehicle_name;
                        this.availableDriversCount = json.available_drivers_count;
                        this.drivers = json.drivers || [];
                        this.quote = json.breakdown;
                        this.quoteError = null;

                        // Trip details changing (vehicle, distance, passengers...)
                        // can move the total enough to make an already-applied
                        // coupon invalid (below its minimum fare) or change what
                        // it's worth — silently re-check it against the fresh total.
                        if (this.appliedCoupon) {
                            this.applyCoupon(true);
                        }
                    })
                    .catch(() => {
                        if (requestId !== this.quoteRequestId) return;
                        this.quote = null;
                        this.quoteError = @json(__('Unable to calculate distance.'));
                    })
                    .finally(() => {
                        if (requestId === this.quoteRequestId) this.calculating = false;
                    });
            },
        };
    }
</script>
@endif
