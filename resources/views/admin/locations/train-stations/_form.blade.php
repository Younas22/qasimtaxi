@php $model = $model ?? null; @endphp

<div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <x-admin.input-label for="city_id" value="{{ __('City') }}" />
            <select id="city_id" name="city_id" required
                class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
                <option value="">{{ __('Select a city') }}</option>
                @foreach ($cities->groupBy(fn ($city) => $city->country->name) as $countryName => $countryCities)
                    <optgroup label="{{ $countryName }}">
                        @foreach ($countryCities as $city)
                            <option value="{{ $city->id }}" @selected(old('city_id', $model?->city_id) == $city->id)>{{ $city->name }} ({{ $city->state->name }})</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
            <x-admin.input-error :messages="$errors->get('city_id')" />
        </div>

        <div>
            <x-admin.input-label for="name" value="{{ __('Station Name') }}" />
            <x-admin.text-input id="name" name="name" type="text" value="{{ old('name', $model?->name) }}" placeholder="{{ __('e.g. Grand Central Terminal') }}" required autofocus />
            <x-admin.input-error :messages="$errors->get('name')" />
        </div>

        <div>
            <x-admin.input-label for="code" value="{{ __('Station Code') }}" />
            <x-admin.text-input id="code" name="code" type="text" value="{{ old('code', $model?->code) }}" placeholder="{{ __('Optional') }}" maxlength="10" />
            <x-admin.input-error :messages="$errors->get('code')" />
        </div>
    </div>

    <x-admin.google-fields :model="$model" />
</div>
