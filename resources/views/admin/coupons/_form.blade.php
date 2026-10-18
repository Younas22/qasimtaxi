@php $coupon = $coupon ?? null; @endphp

<div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <div>
            <x-admin.input-label for="code" value="{{ __('Coupon Code') }}" />
            <x-admin.text-input id="code" name="code" type="text" value="{{ old('code', $coupon?->code) }}" placeholder="{{ __('e.g. WELCOME10') }}" required autofocus class="uppercase" />
            <x-admin.input-error :messages="$errors->get('code')" />
        </div>

        <div>
            <x-admin.input-label for="description" value="{{ __('Description') }}" />
            <x-admin.text-input id="description" name="description" type="text" value="{{ old('description', $coupon?->description) }}" placeholder="{{ __('e.g. 10% off your first ride') }}" />
            <x-admin.input-error :messages="$errors->get('description')" />
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
        <div>
            <x-admin.input-label for="discount_type" value="{{ __('Discount Type') }}" />
            <select id="discount_type" name="discount_type" required
                class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
                @foreach (\App\Models\Coupon::DISCOUNT_TYPES as $value => $label)
                    <option value="{{ $value }}" @selected(old('discount_type', $coupon?->discount_type ?? 'percentage') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <x-admin.input-error :messages="$errors->get('discount_type')" />
        </div>

        <div>
            <x-admin.input-label for="discount_value" value="{{ __('Discount Value') }}" />
            <x-admin.text-input id="discount_value" name="discount_value" type="number" step="0.01" min="0.01" value="{{ old('discount_value', $coupon?->discount_value) }}" required />
            <x-admin.input-error :messages="$errors->get('discount_value')" />
        </div>

        <div>
            <x-admin.input-label for="max_discount" value="{{ __('Max Discount (optional)') }}" />
            <x-admin.text-input id="max_discount" name="max_discount" type="number" step="0.01" min="0" value="{{ old('max_discount', $coupon?->max_discount) }}" placeholder="{{ __('For percentage coupons') }}" />
            <x-admin.input-error :messages="$errors->get('max_discount')" />
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
        <div>
            <x-admin.input-label for="min_fare" value="{{ __('Minimum Fare (optional)') }}" />
            <x-admin.text-input id="min_fare" name="min_fare" type="number" step="0.01" min="0" value="{{ old('min_fare', $coupon?->min_fare) }}" />
            <x-admin.input-error :messages="$errors->get('min_fare')" />
        </div>

        <div>
            <x-admin.input-label for="usage_limit" value="{{ __('Usage Limit (optional)') }}" />
            <x-admin.text-input id="usage_limit" name="usage_limit" type="number" min="1" value="{{ old('usage_limit', $coupon?->usage_limit) }}" placeholder="{{ __('Unlimited') }}" />
            <x-admin.input-error :messages="$errors->get('usage_limit')" />
        </div>

        <div>
            <x-admin.input-label for="expires_at" value="{{ __('Expires At (optional)') }}" />
            <x-admin.text-input id="expires_at" name="expires_at" type="date" value="{{ old('expires_at', $coupon?->expires_at?->format('Y-m-d')) }}" />
            <x-admin.input-error :messages="$errors->get('expires_at')" />
        </div>
    </div>

    <label class="flex items-center gap-2">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $coupon?->is_active ?? true)) class="h-4 w-4 rounded border-luxury-border bg-luxury-charcoal text-luxury-gold focus:ring-1 focus:ring-luxury-gold">
        <span class="text-sm text-luxury-white">{{ __('Active') }}</span>
    </label>
</div>
