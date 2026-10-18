@php
    $booking = $booking ?? null;
    $stops = old('stops', $booking?->stops ?? []);
@endphp

<div class="space-y-6"
    x-data="bookingForm({
        vehicles: {{ \Illuminate\Support\Js::from($vehicles->map(fn ($v) => [
            'id' => $v->id,
            'categoryId' => $v->vehicle_category_id,
        ])) }},
        pricingRules: {{ \Illuminate\Support\Js::from($pricingRules) }},
        type: {{ \Illuminate\Support\Js::from(old('type', $booking?->type ?? 'one_way')) }},
        vehicleId: {{ old('vehicle_id', $booking?->vehicle_id) ?: 'null' }},
        distanceKm: {{ \Illuminate\Support\Js::from(old('distance_km', $booking?->distance_km)) }},
        hours: {{ \Illuminate\Support\Js::from(old('hours', $booking?->hours)) }},
        pickupDate: {{ \Illuminate\Support\Js::from(old('pickup_date', $booking?->pickup_datetime?->format('Y-m-d'))) }},
        pickupTime: {{ \Illuminate\Support\Js::from(old('pickup_time', $booking?->pickup_datetime?->format('H:i'))) }},
        waitingMinutes: {{ \Illuminate\Support\Js::from((int) old('waiting_minutes', $booking?->waiting_minutes ?? 0)) }},
        hasToll: {{ \Illuminate\Support\Js::from((bool) old('has_toll', $booking?->has_toll ?? false)) }},
        passengers: {{ \Illuminate\Support\Js::from((int) old('passengers', $booking?->passengers ?? 1)) }},
        stops: {{ \Illuminate\Support\Js::from(array_values($stops) ?: ['']) }},
    })">

    {{-- Booking Details --}}
    <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
        <h3 class="text-sm font-semibold text-luxury-white">{{ __('Booking Details') }}</h3>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <div>
                <x-admin.input-label for="customer_id" value="{{ __('Customer') }}" />
                <select id="customer_id" name="customer_id" required
                    class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
                    <option value="">{{ __('Select a customer') }}</option>
                    @foreach ($customers as $c)
                        <option value="{{ $c->id }}" @selected(old('customer_id', $booking?->customer_id) == $c->id)>{{ $c->name }} ({{ $c->email }})</option>
                    @endforeach
                </select>
                <x-admin.input-error :messages="$errors->get('customer_id')" />
            </div>

            <div>
                <x-admin.input-label for="vehicle_id" value="{{ __('Vehicle') }}" />
                <select id="vehicle_id" name="vehicle_id" x-model.number="vehicleId" required
                    class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
                    <option value="">{{ __('Select a vehicle') }}</option>
                    @foreach ($vehicles as $v)
                        <option value="{{ $v->id }}">{{ $v->name }} &mdash; {{ $v->category?->name }}</option>
                    @endforeach
                </select>
                <x-admin.input-error :messages="$errors->get('vehicle_id')" />
            </div>

            <div @if (isset($booking)) x-data="{
                suggesting: false,
                suggestion: null,
                suggestionText() {
                    if (! this.suggestion || ! this.suggestion.found) return '';
                    const s = this.suggestion;
                    if (s.status === 'busy') {
                        return '{{ __('Suggested') }}: ' + s.driver_name + ' — {{ __('busy now, free in') }} ' + s.ride_ends_in_minutes + ' {{ __('min, then') }} ' + s.duration_minutes + ' {{ __('min to pickup') }} (' + s.distance_km + ' km)';
                    }
                    return '{{ __('Suggested') }}: ' + s.driver_name + ' — ' + s.distance_km + ' km, ' + s.duration_minutes + ' {{ __('min ETA') }}';
                },
            }" @endif>
                <div class="flex items-center justify-between">
                    <x-admin.input-label for="driver_id" value="{{ __('Driver (optional)') }}" />
                    @if (isset($booking))
                        <button type="button" :disabled="suggesting"
                            @click="
                                suggesting = true;
                                suggestion = null;
                                fetch('{{ route('admin.bookings.suggest-driver', $booking) }}', {
                                    method: 'POST',
                                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                                })
                                    .then(r => r.json())
                                    .then(data => {
                                        suggesting = false;
                                        if (data.found) {
                                            document.getElementById('driver_id').value = data.driver_id;
                                            suggestion = data;
                                        } else {
                                            suggestion = { found: false };
                                        }
                                    })
                                    .catch(() => { suggesting = false; });
                            "
                            class="tap-scale text-xs font-medium text-luxury-gold hover:text-luxury-gold-light disabled:opacity-50">
                            <span x-show="!suggesting">{{ __('Suggest Best Driver') }}</span>
                            <span x-show="suggesting" x-cloak>{{ __('Finding best driver…') }}</span>
                        </button>
                    @endif
                </div>
                <select id="driver_id" name="driver_id"
                    class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
                    <option value="">{{ __('Unassigned') }}</option>
                    @foreach ($drivers as $d)
                        <option value="{{ $d->id }}" @selected(old('driver_id', $booking?->driver_id) == $d->id)>{{ $d->name }} ({{ $d->status_label }})</option>
                    @endforeach
                </select>
                @if (isset($booking))
                    <template x-if="suggestion && suggestion.found">
                        <p class="mt-1.5 text-xs" :class="suggestion.status === 'busy' ? 'text-amber-400' : 'text-emerald-400'" x-text="suggestionText()"></p>
                    </template>
                    <template x-if="suggestion && suggestion.found === false">
                        <p class="mt-1.5 text-xs text-luxury-muted">{{ __('No available or busy driver found.') }}</p>
                    </template>
                @endif
                <x-admin.input-error :messages="$errors->get('driver_id')" />
            </div>

            <div>
                <x-admin.input-label for="type" value="{{ __('Booking Type') }}" />
                <select id="type" name="type" x-model="type" required
                    class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
                    @foreach ($types as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                <x-admin.input-error :messages="$errors->get('type')" />
            </div>

            <div>
                <x-admin.input-label for="status" value="{{ __('Status') }}" />
                <select id="status" name="status" required
                    class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $booking?->status ?? 'pending') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <x-admin.input-error :messages="$errors->get('status')" />
            </div>
        </div>
    </div>

    {{-- Trip Details --}}
    <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
        <h3 class="text-sm font-semibold text-luxury-white">{{ __('Trip Details') }}</h3>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div>
                <x-admin.input-label for="pickup_location" value="{{ __('Pickup') }}" />
                <x-admin.text-input id="pickup_location" name="pickup_location" type="text" value="{{ old('pickup_location', $booking?->pickup_location) }}" required autofocus />
                <x-admin.input-error :messages="$errors->get('pickup_location')" />
            </div>

            <div>
                <x-admin.input-label for="dropoff_location" value="{{ __('Dropoff') }}" />
                <x-admin.text-input id="dropoff_location" name="dropoff_location" type="text" value="{{ old('dropoff_location', $booking?->dropoff_location) }}" required />
                <x-admin.input-error :messages="$errors->get('dropoff_location')" />
            </div>
        </div>

        {{-- Stops --}}
        <div>
            <div class="mb-2 flex items-center justify-between">
                <x-admin.input-label value="{{ __('Stops (optional)') }}" class="!mb-0" />
                <button type="button" @click="stops.push('')" class="text-xs font-medium text-luxury-gold hover:text-luxury-gold-light">{{ __('+ Add Stop') }}</button>
            </div>
            <div class="space-y-2">
                <template x-for="(stop, index) in stops" :key="index">
                    <div class="flex items-center gap-2">
                        <input type="text" :name="'stops[' + index + ']'" x-model="stops[index]" placeholder="{{ __('Stop address') }}"
                            class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-2.5 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                        <button type="button" @click="stops.splice(index, 1)" class="shrink-0 rounded-lg border border-red-500/30 px-2.5 py-2.5 text-red-400 hover:bg-red-500/10">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </template>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <x-admin.input-label for="pickup_date" value="{{ __('Pickup Date') }}" />
                <x-admin.text-input id="pickup_date" name="pickup_date" type="date" x-model="pickupDate" required />
                <x-admin.input-error :messages="$errors->get('pickup_date')" />
            </div>

            <div>
                <x-admin.input-label for="pickup_time" value="{{ __('Pickup Time') }}" />
                <x-admin.text-input id="pickup_time" name="pickup_time" type="time" x-model="pickupTime" required />
                <x-admin.input-error :messages="$errors->get('pickup_time')" />
            </div>

            <div x-show="type === 'round_trip'" x-cloak>
                <x-admin.input-label for="return_date" value="{{ __('Return Date') }}" />
                <x-admin.text-input id="return_date" name="return_date" type="date" value="{{ old('return_date', $booking?->return_datetime?->format('Y-m-d')) }}" />
                <x-admin.input-error :messages="$errors->get('return_date')" />
            </div>

            <div x-show="type === 'round_trip'" x-cloak>
                <x-admin.input-label for="return_time" value="{{ __('Return Time') }}" />
                <x-admin.text-input id="return_time" name="return_time" type="time" value="{{ old('return_time', $booking?->return_datetime?->format('H:i')) }}" />
                <x-admin.input-error :messages="$errors->get('return_time')" />
            </div>

            <div x-show="type === 'hourly'" x-cloak>
                <x-admin.input-label for="hours" value="{{ __('Duration (hours)') }}" />
                <x-admin.text-input id="hours" name="hours" type="number" min="1" max="24" x-model="hours" />
                <x-admin.input-error :messages="$errors->get('hours')" />
            </div>

            <div x-show="type !== 'hourly'" x-cloak>
                <x-admin.input-label for="distance_km" value="{{ __('Distance (km)') }}" />
                <x-admin.text-input id="distance_km" name="distance_km" type="number" step="0.01" min="0" x-model="distanceKm" />
                <x-admin.input-error :messages="$errors->get('distance_km')" />
            </div>
        </div>
    </div>

    {{-- Passenger Details --}}
    <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
        <h3 class="text-sm font-semibold text-luxury-white">{{ __('Passenger Details') }}</h3>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div>
                <x-admin.input-label for="passengers" value="{{ __('Passengers') }}" />
                <x-admin.text-input id="passengers" name="passengers" type="number" min="1" max="60" x-model.number="passengers" value="{{ old('passengers', $booking?->passengers ?? 1) }}" required />
                <x-admin.input-error :messages="$errors->get('passengers')" />
            </div>

            <div>
                <x-admin.input-label for="luggage" value="{{ __('Luggage') }}" />
                <x-admin.text-input id="luggage" name="luggage" type="number" min="0" max="60" value="{{ old('luggage', $booking?->luggage ?? 0) }}" required />
                <x-admin.input-error :messages="$errors->get('luggage')" />
            </div>

            <div>
                <x-admin.input-label for="waiting_minutes" value="{{ __('Waiting Time (minutes)') }}" />
                <x-admin.text-input id="waiting_minutes" name="waiting_minutes" type="number" min="0" max="1440" x-model.number="waitingMinutes" required />
                <x-admin.input-error :messages="$errors->get('waiting_minutes')" />
            </div>

            <div class="flex items-end pb-1">
                <label class="flex items-center gap-2 text-sm text-luxury-muted">
                    <input type="hidden" name="has_toll" value="0">
                    <input type="checkbox" id="has_toll" name="has_toll" value="1" x-model="hasToll"
                        class="h-4 w-4 rounded border-luxury-border bg-luxury-charcoal text-luxury-gold focus:ring-1 focus:ring-luxury-gold">
                    {{ __('Trip includes toll roads') }}
                </label>
            </div>

            <div class="sm:col-span-2">
                <x-admin.input-label for="notes" value="{{ __('Notes') }}" />
                <textarea id="notes" name="notes" rows="3" placeholder="{{ __('Special instructions, flight number, etc.') }}"
                    class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">{{ old('notes', $booking?->notes) }}</textarea>
                <x-admin.input-error :messages="$errors->get('notes')" />
            </div>
        </div>
    </div>

    {{-- Pricing --}}
    <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
        <h3 class="text-sm font-semibold text-luxury-white">{{ __('Pricing') }}</h3>

        <template x-if="activeRule()">
            <div class="rounded-xl border border-luxury-border/60 bg-luxury-graphite/40 p-4">
                <p class="mb-3 text-xs font-medium uppercase tracking-wider text-luxury-muted">
                    {{ __('Fare Breakdown') }} <span class="text-luxury-gold" x-text="'(' + activeRule().label + ')'"></span>
                </p>
                <dl class="space-y-1.5 text-sm">
                    <div class="flex items-center justify-between" x-show="breakdown().base_fare > 0">
                        <dt class="text-luxury-muted">{{ __('Base Fare') }}</dt>
                        <dd class="text-luxury-white" x-text="money(breakdown().base_fare)"></dd>
                    </div>
                    <div class="flex items-center justify-between" x-show="breakdown().distance_fare > 0">
                        <dt class="text-luxury-muted">{{ __('KM Fare') }}</dt>
                        <dd class="text-luxury-white" x-text="money(breakdown().distance_fare)"></dd>
                    </div>
                    <div class="flex items-center justify-between" x-show="breakdown().hour_fare > 0">
                        <dt class="text-luxury-muted">{{ __('Hour Fare') }}</dt>
                        <dd class="text-luxury-white" x-text="money(breakdown().hour_fare)"></dd>
                    </div>
                    <div class="flex items-center justify-between" x-show="breakdown().waiting_charge > 0">
                        <dt class="text-luxury-muted">{{ __('Waiting Charges') }}</dt>
                        <dd class="text-luxury-white" x-text="money(breakdown().waiting_charge)"></dd>
                    </div>
                    <div class="flex items-center justify-between" x-show="breakdown().night_charge > 0">
                        <dt class="text-luxury-muted">{{ __('Night Charges') }}</dt>
                        <dd class="text-luxury-white" x-text="money(breakdown().night_charge)"></dd>
                    </div>
                    <div class="flex items-center justify-between" x-show="breakdown().weekend_charge > 0">
                        <dt class="text-luxury-muted">{{ __('Weekend Charges') }}</dt>
                        <dd class="text-luxury-white" x-text="money(breakdown().weekend_charge)"></dd>
                    </div>
                    <div class="flex items-center justify-between" x-show="breakdown().toll_charge > 0">
                        <dt class="text-luxury-muted">{{ __('Toll Charges') }}</dt>
                        <dd class="text-luxury-white" x-text="money(breakdown().toll_charge)"></dd>
                    </div>
                    <div class="flex items-center justify-between" x-show="breakdown().airport_surcharge > 0">
                        <dt class="text-luxury-muted">{{ __('Airport Surcharge') }}</dt>
                        <dd class="text-luxury-white" x-text="money(breakdown().airport_surcharge)"></dd>
                    </div>
                    <div class="flex items-center justify-between" x-show="breakdown().service_fee > 0">
                        <dt class="text-luxury-muted">{{ __('Service Fee') }}</dt>
                        <dd class="text-luxury-white" x-text="money(breakdown().service_fee)"></dd>
                    </div>
                    <div class="flex items-center justify-between" x-show="breakdown().extra_passenger_charge > 0">
                        <dt class="text-luxury-muted">{{ __('Extra Passenger Charge') }}</dt>
                        <dd class="text-luxury-white" x-text="money(breakdown().extra_passenger_charge)"></dd>
                    </div>
                    <div class="mt-2 flex items-center justify-between border-t border-luxury-border/60 pt-2 font-semibold">
                        <dt class="text-luxury-white">{{ __('Suggested Total') }}</dt>
                        <dd class="text-luxury-gold" x-text="money(breakdown().total)"></dd>
                    </div>
                </dl>
            </div>
        </template>

        <div>
            <x-admin.input-label for="fare_amount" value="{{ __('Fare Amount') }}" />
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <x-admin.text-input id="fare_amount" name="fare_amount" type="number" step="0.01" min="0" value="{{ old('fare_amount', $booking?->fare_amount) }}" required class="sm:max-w-xs" />
                <template x-if="activeRule()">
                    <button type="button" @click="document.getElementById('fare_amount').value = breakdown().total"
                        class="whitespace-nowrap text-xs text-luxury-muted hover:text-luxury-gold">
                        {{ __('Use suggested total —') }} <span class="font-semibold text-luxury-gold underline" x-text="money(breakdown().total)"></span>
                    </button>
                </template>
            </div>
            <p class="mt-2 text-xs text-luxury-muted">{{ __("Auto-estimated by the dynamic pricing engine from the vehicle's category pricing. You can override it.") }}</p>
            <x-admin.input-error :messages="$errors->get('fare_amount')" />
        </div>
    </div>
</div>

<script>
    function bookingForm(config) {
        return {
            vehicles: config.vehicles,
            pricingRules: config.pricingRules,
            type: config.type,
            vehicleId: config.vehicleId,
            distanceKm: config.distanceKm,
            hours: config.hours,
            pickupDate: config.pickupDate,
            pickupTime: config.pickupTime,
            waitingMinutes: config.waitingMinutes,
            hasToll: config.hasToll,
            passengers: config.passengers,
            stops: config.stops.length ? config.stops : [''],

            activeRule() {
                const vehicle = this.vehicles.find((v) => v.id === Number(this.vehicleId));
                if (!vehicle) return this.pricingRules['global'] ?? null;

                const rule = vehicle.categoryId ? this.pricingRules[vehicle.categoryId] : null;

                return (rule && rule.is_active) ? rule : (this.pricingRules['global'] ?? null);
            },

            isNight(rule) {
                if (!this.pickupTime) return false;

                const time = this.pickupTime + ':00';
                const start = rule.night_start_time;
                const end = rule.night_end_time;
                if (start === end) return false;

                return start > end ? (time >= start || time < end) : (time >= start && time < end);
            },

            isWeekend(rule) {
                if (!this.pickupDate) return false;

                const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                const dayName = days[new Date(this.pickupDate + 'T00:00:00').getDay()];

                return (rule.weekend_days || []).includes(dayName);
            },

            breakdown() {
                const rule = this.activeRule();
                const empty = { base_fare: 0, distance_fare: 0, hour_fare: 0, waiting_charge: 0, night_charge: 0, weekend_charge: 0, toll_charge: 0, airport_surcharge: 0, service_fee: 0, extra_passenger_charge: 0, total: 0 };
                if (!rule) return empty;

                const distance = parseFloat(this.distanceKm) || 0;
                const legMultiplier = this.type === 'round_trip' ? 2 : 1;

                const baseFare = round(rule.base_fare * legMultiplier);
                const billableKm = Math.max(distance - (rule.included_km || 0), 0);
                const distanceFare = this.type === 'hourly' ? 0 : round(rule.km_fare * billableKm * legMultiplier);
                const billableHours = Math.max(Math.max(parseInt(this.hours) || 1, 1) - (rule.included_hours || 0), 0);
                const hourFare = this.type === 'hourly' ? round(rule.hour_fare * billableHours) : 0;

                const chargeableMinutes = Math.max((parseInt(this.waitingMinutes) || 0) - rule.free_waiting_minutes, 0);
                const waitingCharge = round(rule.waiting_charge_per_minute * chargeableMinutes);

                const nightCharge = this.isNight(rule) ? round(rule.night_charge) : 0;
                const weekendCharge = this.isWeekend(rule) ? round(rule.weekend_charge) : 0;
                const tollCharge = this.hasToll ? round(rule.toll_charge) : 0;
                const airportSurcharge = this.type === 'airport_transfer' ? round(rule.airport_surcharge) : 0;
                const serviceFee = round(rule.service_fee);

                const extraPassengers = Math.max((parseInt(this.passengers) || 1) - (rule.included_passengers || 0), 0);
                const extraPassengerCharge = round((rule.extra_passenger_charge || 0) * extraPassengers);

                let total = round(baseFare + distanceFare + hourFare + waitingCharge + nightCharge + weekendCharge + tollCharge + airportSurcharge + serviceFee + extraPassengerCharge);

                if ((rule.minimum_fare || 0) > 0) {
                    total = Math.max(total, round(rule.minimum_fare));
                }

                return { base_fare: baseFare, distance_fare: distanceFare, hour_fare: hourFare, waiting_charge: waitingCharge, night_charge: nightCharge, weekend_charge: weekendCharge, toll_charge: tollCharge, airport_surcharge: airportSurcharge, service_fee: serviceFee, extra_passenger_charge: extraPassengerCharge, total };
            },

            money(value) {
                return Number(value).toFixed(2);
            },
        };
    }

    function round(value) {
        return Math.round(value * 100) / 100;
    }
</script>
