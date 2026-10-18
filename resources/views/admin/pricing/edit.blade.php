@php
    $isGlobal = is_null($category);
    $title = $isGlobal ? __('Global Default Pricing') : __('Pricing — :category', ['category' => $category->name]);
    $action = $isGlobal ? route('admin.pricing.global.update') : route('admin.pricing.category.update', $category);
    $formatTime = fn ($value, $default) => $value ? \Illuminate\Support\Carbon::parse($value)->format('H:i') : $default;
@endphp

<x-admin.layouts.app :title="$title">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ $title }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">
            @if ($isGlobal)
                {{ __("Applied to any fleet category that doesn't have its own custom pricing.") }}
            @else
                {!! __('Custom pricing rule for the <span class="text-luxury-gold">:category</span> category. Saving here overrides the global default for this category.', ['category' => e($category->name)]) !!}
            @endif
        </p>
    </div>

    <form method="POST" action="{{ $action }}">
        @csrf
        @method('PUT')

        <div class="space-y-6">
            {{-- Base Pricing --}}
            <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
                <h3 class="text-sm font-semibold text-luxury-white">{{ __('Base Pricing') }}</h3>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                    <div>
                        <x-admin.input-label for="base_fare" value="{{ __('Base Fare') }}" />
                        <x-admin.text-input id="base_fare" name="base_fare" type="number" step="0.01" min="0" value="{{ old('base_fare', $rule->base_fare ?? 0) }}" required />
                        <x-admin.input-error :messages="$errors->get('base_fare')" />
                    </div>
                    <div>
                        <x-admin.input-label for="base_fare_threshold_km" value="{{ __('Base Fare Applies Up To (km)') }}" />
                        <x-admin.text-input id="base_fare_threshold_km" name="base_fare_threshold_km" type="number" step="0.01" min="0" value="{{ old('base_fare_threshold_km', $rule->base_fare_threshold_km) }}" placeholder="{{ __('e.g. 6 — leave blank to always apply') }}" />
                        <p class="mt-1.5 text-xs text-luxury-muted">{{ __('Trips beyond this total distance skip the base fare and bill purely on distance.') }}</p>
                        <x-admin.input-error :messages="$errors->get('base_fare_threshold_km')" />
                    </div>
                    <div>
                        <x-admin.input-label for="km_fare" value="{{ __('KM Fare (per km)') }}" />
                        <x-admin.text-input id="km_fare" name="km_fare" type="number" step="0.01" min="0" value="{{ old('km_fare', $rule->km_fare ?? 0) }}" required />
                        <x-admin.input-error :messages="$errors->get('km_fare')" />
                    </div>
                    <div>
                        <x-admin.input-label for="hour_fare" value="{{ __('Hour Fare (hourly bookings)') }}" />
                        <x-admin.text-input id="hour_fare" name="hour_fare" type="number" step="0.01" min="0" value="{{ old('hour_fare', $rule->hour_fare ?? 0) }}" required />
                        <x-admin.input-error :messages="$errors->get('hour_fare')" />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 border-t border-luxury-border pt-5 sm:grid-cols-2">
                    <div>
                        <x-admin.input-label for="mid_distance_threshold_km" value="{{ __('Tier 2 Threshold (km)') }}" />
                        <x-admin.text-input id="mid_distance_threshold_km" name="mid_distance_threshold_km" type="number" step="0.01" min="0" value="{{ old('mid_distance_threshold_km', $rule->mid_distance_threshold_km) }}" placeholder="{{ __('e.g. 50') }}" />
                        <x-admin.input-error :messages="$errors->get('mid_distance_threshold_km')" />
                    </div>
                    <div>
                        <x-admin.input-label for="mid_distance_km_fare" value="{{ __('Tier 2 KM Fare') }}" />
                        <x-admin.text-input id="mid_distance_km_fare" name="mid_distance_km_fare" type="number" step="0.01" min="0" value="{{ old('mid_distance_km_fare', $rule->mid_distance_km_fare) }}" placeholder="{{ __('e.g. 1.70') }}" />
                        <x-admin.input-error :messages="$errors->get('mid_distance_km_fare')" />
                    </div>
                    <div>
                        <x-admin.input-label for="long_distance_threshold_km" value="{{ __('Tier 3 Threshold (km)') }}" />
                        <x-admin.text-input id="long_distance_threshold_km" name="long_distance_threshold_km" type="number" step="0.01" min="0" value="{{ old('long_distance_threshold_km', $rule->long_distance_threshold_km) }}" placeholder="{{ __('e.g. 100') }}" />
                        <x-admin.input-error :messages="$errors->get('long_distance_threshold_km')" />
                    </div>
                    <div>
                        <x-admin.input-label for="long_distance_km_fare" value="{{ __('Tier 3 KM Fare') }}" />
                        <x-admin.text-input id="long_distance_km_fare" name="long_distance_km_fare" type="number" step="0.01" min="0" value="{{ old('long_distance_km_fare', $rule->long_distance_km_fare) }}" placeholder="{{ __('e.g. 1.50') }}" />
                        <x-admin.input-error :messages="$errors->get('long_distance_km_fare')" />
                    </div>
                    <div>
                        <x-admin.input-label for="very_long_distance_threshold_km" value="{{ __('Tier 4 Threshold (km)') }}" />
                        <x-admin.text-input id="very_long_distance_threshold_km" name="very_long_distance_threshold_km" type="number" step="0.01" min="0" value="{{ old('very_long_distance_threshold_km', $rule->very_long_distance_threshold_km) }}" placeholder="{{ __('e.g. 200') }}" />
                        <x-admin.input-error :messages="$errors->get('very_long_distance_threshold_km')" />
                    </div>
                    <div>
                        <x-admin.input-label for="very_long_distance_km_fare" value="{{ __('Tier 4 KM Fare') }}" />
                        <x-admin.text-input id="very_long_distance_km_fare" name="very_long_distance_km_fare" type="number" step="0.01" min="0" value="{{ old('very_long_distance_km_fare', $rule->very_long_distance_km_fare) }}" placeholder="{{ __('e.g. 1.25') }}" />
                        <x-admin.input-error :messages="$errors->get('very_long_distance_km_fare')" />
                    </div>
                </div>
            </div>

            {{-- Waiting Charges --}}
            <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
                <h3 class="text-sm font-semibold text-luxury-white">{{ __('Waiting Charges') }}</h3>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <x-admin.input-label for="waiting_charge_per_minute" value="{{ __('Charge per Minute') }}" />
                        <x-admin.text-input id="waiting_charge_per_minute" name="waiting_charge_per_minute" type="number" step="0.01" min="0" value="{{ old('waiting_charge_per_minute', $rule->waiting_charge_per_minute ?? 0) }}" required />
                        <x-admin.input-error :messages="$errors->get('waiting_charge_per_minute')" />
                    </div>
                    <div>
                        <x-admin.input-label for="free_waiting_minutes" value="{{ __('Free Waiting Minutes') }}" />
                        <x-admin.text-input id="free_waiting_minutes" name="free_waiting_minutes" type="number" min="0" value="{{ old('free_waiting_minutes', $rule->free_waiting_minutes ?? 0) }}" required />
                        <x-admin.input-error :messages="$errors->get('free_waiting_minutes')" />
                    </div>
                </div>
            </div>

            {{-- Night Charges --}}
            <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
                <h3 class="text-sm font-semibold text-luxury-white">{{ __('Night Charges') }}</h3>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                    <div>
                        <x-admin.input-label for="night_charge" value="{{ __('Night Charge') }}" />
                        <x-admin.text-input id="night_charge" name="night_charge" type="number" step="0.01" min="0" value="{{ old('night_charge', $rule->night_charge ?? 0) }}" required />
                        <x-admin.input-error :messages="$errors->get('night_charge')" />
                    </div>
                    <div>
                        <x-admin.input-label for="night_start_time" value="{{ __('Night Window Start') }}" />
                        <x-admin.text-input id="night_start_time" name="night_start_time" type="time" value="{{ old('night_start_time', $formatTime($rule->night_start_time ?? null, '22:00')) }}" required />
                        <x-admin.input-error :messages="$errors->get('night_start_time')" />
                    </div>
                    <div>
                        <x-admin.input-label for="night_end_time" value="{{ __('Night Window End') }}" />
                        <x-admin.text-input id="night_end_time" name="night_end_time" type="time" value="{{ old('night_end_time', $formatTime($rule->night_end_time ?? null, '06:00')) }}" required />
                        <x-admin.input-error :messages="$errors->get('night_end_time')" />
                    </div>
                </div>
            </div>

            {{-- Weekend Charges --}}
            <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
                <h3 class="text-sm font-semibold text-luxury-white">{{ __('Weekend Charges') }}</h3>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <x-admin.input-label for="weekend_charge" value="{{ __('Weekend Charge') }}" />
                        <x-admin.text-input id="weekend_charge" name="weekend_charge" type="number" step="0.01" min="0" value="{{ old('weekend_charge', $rule->weekend_charge ?? 0) }}" required />
                        <x-admin.input-error :messages="$errors->get('weekend_charge')" />
                    </div>
                    <div>
                        <x-admin.input-label value="{{ __('Weekend Days') }}" />
                        @php $weekendDays = old('weekend_days', $rule->weekend_days ?? ['Saturday', 'Sunday']); @endphp
                        <div class="flex flex-wrap gap-3">
                            @foreach (\App\Models\PricingRule::WEEKDAYS as $day)
                                <label class="flex items-center gap-1.5 text-xs text-luxury-muted">
                                    <input type="checkbox" name="weekend_days[]" value="{{ $day }}" @checked(in_array($day, $weekendDays, true))
                                        class="h-4 w-4 rounded border-luxury-border bg-luxury-charcoal text-luxury-gold focus:ring-1 focus:ring-luxury-gold">
                                    {{ substr($day, 0, 3) }}
                                </label>
                            @endforeach
                        </div>
                        <x-admin.input-error :messages="$errors->get('weekend_days')" />
                    </div>
                </div>
            </div>

            {{-- Included Allowances & Minimum Fare --}}
            <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
                <h3 class="text-sm font-semibold text-luxury-white">{{ __('Included Allowances & Minimum Fare') }}</h3>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                    <div>
                        <x-admin.input-label for="minimum_fare" value="{{ __('Minimum Fare') }}" />
                        <x-admin.text-input id="minimum_fare" name="minimum_fare" type="number" step="0.01" min="0" value="{{ old('minimum_fare', $rule->minimum_fare ?? 0) }}" required />
                        <p class="mt-1 text-xs text-luxury-muted">{{ __('Total never drops below this, if set above 0.') }}</p>
                        <x-admin.input-error :messages="$errors->get('minimum_fare')" />
                    </div>
                    <div>
                        <x-admin.input-label for="included_km" value="{{ __('Included KM') }}" />
                        <x-admin.text-input id="included_km" name="included_km" type="number" step="0.01" min="0" value="{{ old('included_km', $rule->included_km ?? 0) }}" required />
                        <p class="mt-1 text-xs text-luxury-muted">{{ __('Distance covered by the base fare before per-km charges apply.') }}</p>
                        <x-admin.input-error :messages="$errors->get('included_km')" />
                    </div>
                    <div>
                        <x-admin.input-label for="included_hours" value="{{ __('Included Hours') }}" />
                        <x-admin.text-input id="included_hours" name="included_hours" type="number" step="0.01" min="0" value="{{ old('included_hours', $rule->included_hours ?? 0) }}" required />
                        <p class="mt-1 text-xs text-luxury-muted">{{ __('Hours covered by the base fare on hourly bookings.') }}</p>
                        <x-admin.input-error :messages="$errors->get('included_hours')" />
                    </div>
                    <div>
                        <x-admin.input-label for="included_passengers" value="{{ __('Included Passengers') }}" />
                        <x-admin.text-input id="included_passengers" name="included_passengers" type="number" min="0" value="{{ old('included_passengers', $rule->included_passengers ?? 4) }}" required />
                        <x-admin.input-error :messages="$errors->get('included_passengers')" />
                    </div>
                    <div>
                        <x-admin.input-label for="extra_passenger_charge" value="{{ __('Extra Passenger Charge') }}" />
                        <x-admin.text-input id="extra_passenger_charge" name="extra_passenger_charge" type="number" step="0.01" min="0" value="{{ old('extra_passenger_charge', $rule->extra_passenger_charge ?? 0) }}" required />
                        <p class="mt-1 text-xs text-luxury-muted">{{ __('Charged per passenger beyond the included count.') }}</p>
                        <x-admin.input-error :messages="$errors->get('extra_passenger_charge')" />
                    </div>
                </div>
            </div>

            {{-- Pickup Approach Distance --}}
            <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
                <div>
                    <h3 class="text-sm font-semibold text-luxury-white">{{ __('Pickup Approach Distance') }}</h3>
                    <p class="mt-1 text-xs text-luxury-muted">{{ __("Charge for the driver's run to the pickup point. For short rides it is measured from the nearest online driver's live location; for longer rides (or when no driver is online) from your office. The first free km are on the driver; every km beyond that is added to the fare. Example: free 5 km, pickup 10 km away → 5 km charged. Set the rate to 0 to turn this off.") }}</p>
                </div>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                    <div>
                        <x-admin.input-label for="approach_free_km" value="{{ __('Free Approach KM') }}" />
                        <x-admin.text-input id="approach_free_km" name="approach_free_km" type="number" step="0.01" min="0" value="{{ old('approach_free_km', $rule->approach_free_km ?? 0) }}" required />
                        <p class="mt-1 text-xs text-luxury-muted">{{ __('Km from the office to the pickup that are not charged.') }}</p>
                        <x-admin.input-error :messages="$errors->get('approach_free_km')" />
                    </div>
                    <div>
                        <x-admin.input-label for="approach_km_fare" value="{{ __('Approach Charge per KM') }}" />
                        <x-admin.text-input id="approach_km_fare" name="approach_km_fare" type="number" step="0.01" min="0" value="{{ old('approach_km_fare', $rule->approach_km_fare ?? 0) }}" required />
                        <p class="mt-1 text-xs text-luxury-muted">{{ __('Charged per km beyond the free approach km.') }}</p>
                        <x-admin.input-error :messages="$errors->get('approach_km_fare')" />
                    </div>
                    <div>
                        <x-admin.input-label for="approach_driver_max_km" value="{{ __('Use Driver Location Below (KM)') }}" />
                        <x-admin.text-input id="approach_driver_max_km" name="approach_driver_max_km" type="number" step="0.01" min="0" value="{{ old('approach_driver_max_km', $rule->approach_driver_max_km ?? 50) }}" required />
                        <p class="mt-1 text-xs text-luxury-muted">{{ __('Rides shorter than this measure from the nearest driver; this distance and longer measure from the office. 0 = always office.') }}</p>
                        <x-admin.input-error :messages="$errors->get('approach_driver_max_km')" />
                    </div>
                </div>
            </div>

            {{-- Surcharges & Fees --}}
            <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
                <h3 class="text-sm font-semibold text-luxury-white">{{ __('Surcharges & Fees') }}</h3>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                    <div>
                        <x-admin.input-label for="toll_charge" value="{{ __('Toll Charge') }}" />
                        <x-admin.text-input id="toll_charge" name="toll_charge" type="number" step="0.01" min="0" value="{{ old('toll_charge', $rule->toll_charge ?? 0) }}" required />
                        <x-admin.input-error :messages="$errors->get('toll_charge')" />
                    </div>
                    <div>
                        <x-admin.input-label for="airport_surcharge" value="{{ __('Airport Surcharge') }}" />
                        <x-admin.text-input id="airport_surcharge" name="airport_surcharge" type="number" step="0.01" min="0" value="{{ old('airport_surcharge', $rule->airport_surcharge ?? 0) }}" required />
                        <x-admin.input-error :messages="$errors->get('airport_surcharge')" />
                    </div>
                    <div>
                        <x-admin.input-label for="service_fee" value="{{ __('Service Fee') }}" />
                        <x-admin.text-input id="service_fee" name="service_fee" type="number" step="0.01" min="0" value="{{ old('service_fee', $rule->service_fee ?? 0) }}" required />
                        <x-admin.input-error :messages="$errors->get('service_fee')" />
                    </div>
                </div>

                @unless ($isGlobal)
                    <label class="flex items-center gap-2 text-sm text-luxury-muted">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $rule->is_active ?? true))
                            class="h-4 w-4 rounded border-luxury-border bg-luxury-charcoal text-luxury-gold focus:ring-1 focus:ring-luxury-gold">
                        {{ __('Use this custom pricing for :category (uncheck to fall back to the global default)', ['category' => $category->name]) }}
                    </label>
                @endunless
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end gap-3">
            <a href="{{ route('admin.pricing.index') }}" class="rounded-lg border border-luxury-border px-4 py-2.5 text-sm font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                {{ __('Cancel') }}
            </a>
            <x-admin.button type="submit" variant="primary">{{ __('Save Pricing') }}</x-admin.button>
        </div>
    </form>
</x-admin.layouts.app>
