@php $review = $review ?? null; @endphp

<div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <div>
            <x-admin.input-label for="customer_id" value="{{ __('Customer') }}" />
            <select id="customer_id" name="customer_id" required
                class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
                <option value="">{{ __('Select a customer') }}</option>
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}" @selected(old('customer_id', $review?->customer_id) == $customer->id)>{{ $customer->name }} ({{ $customer->email }})</option>
                @endforeach
            </select>
            <x-admin.input-error :messages="$errors->get('customer_id')" />
            <p class="mt-1 text-xs text-luxury-muted">{{ __("Don't see the customer?") }} <a href="{{ route('admin.customers.create') }}" class="text-luxury-gold hover:underline">{{ __('Add one first') }}</a>.</p>
        </div>

        <div>
            <x-admin.input-label value="{{ __('Rating') }}" />
            <x-admin.rating-input name="rating" :value="old('rating', $review?->rating ?? 5)" />
            <x-admin.input-error :messages="$errors->get('rating')" />
        </div>

        <div>
            <x-admin.input-label for="driver_id" value="{{ __('Driver (optional)') }}" />
            <select id="driver_id" name="driver_id"
                class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
                <option value="">{{ __('None') }}</option>
                @foreach ($drivers as $driver)
                    <option value="{{ $driver->id }}" @selected(old('driver_id', $review?->driver_id) == $driver->id)>{{ $driver->name }}</option>
                @endforeach
            </select>
            <x-admin.input-error :messages="$errors->get('driver_id')" />
        </div>

        <div>
            <x-admin.input-label for="vehicle_id" value="{{ __('Vehicle (optional)') }}" />
            <select id="vehicle_id" name="vehicle_id"
                class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
                <option value="">{{ __('None') }}</option>
                @foreach ($vehicles as $vehicle)
                    <option value="{{ $vehicle->id }}" @selected(old('vehicle_id', $review?->vehicle_id) == $vehicle->id)>{{ $vehicle->name }}</option>
                @endforeach
            </select>
            <x-admin.input-error :messages="$errors->get('vehicle_id')" />
        </div>

        <div>
            <x-admin.input-label for="status" value="{{ __('Status') }}" />
            <select id="status" name="status" required
                class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
                @foreach (\App\Models\Review::STATUSES as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $review?->status ?? 'approved') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <x-admin.input-error :messages="$errors->get('status')" />
            <p class="mt-1 text-xs text-luxury-muted">{{ __('Only "Approved" reviews with a comment can appear as public testimonials.') }}</p>
        </div>
    </div>

    <div>
        <x-admin.input-label for="comment" value="{{ __('Comment') }}" />
        <textarea id="comment" name="comment" rows="4" placeholder="{{ __('What did the customer say about their ride?') }}"
            class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">{{ old('comment', $review?->comment) }}</textarea>
        <x-admin.input-error :messages="$errors->get('comment')" />
    </div>
</div>
