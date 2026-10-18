@props(['model' => null])

<div x-data="{ open: {{ old('google_place_id', $model?->google_place_id) || old('latitude', $model?->latitude) ? 'true' : 'false' }} }" class="border-t border-luxury-border pt-5">
    <button type="button" @click="open = !open" class="flex items-center gap-2 text-xs font-medium uppercase tracking-wide text-luxury-muted hover:text-luxury-gold">
        <svg class="h-3.5 w-3.5 transition-transform" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
        </svg>
        {{ __('Google Places (optional)') }}
    </button>

    <div x-show="open" x-cloak x-transition class="mt-4 grid grid-cols-1 gap-5 sm:grid-cols-3">
        <div class="sm:col-span-1">
            <x-admin.input-label for="google_place_id" value="{{ __('Google Place ID') }}" />
            <x-admin.text-input id="google_place_id" name="google_place_id" type="text" value="{{ old('google_place_id', $model?->google_place_id) }}" placeholder="{{ __('ChIJ...') }}" />
            <x-admin.input-error :messages="$errors->get('google_place_id')" />
        </div>

        <div>
            <x-admin.input-label for="latitude" value="{{ __('Latitude') }}" />
            <x-admin.text-input id="latitude" name="latitude" type="number" step="0.0000001" min="-90" max="90" value="{{ old('latitude', $model?->latitude) }}" placeholder="{{ __('e.g. 34.0522') }}" />
            <x-admin.input-error :messages="$errors->get('latitude')" />
        </div>

        <div>
            <x-admin.input-label for="longitude" value="{{ __('Longitude') }}" />
            <x-admin.text-input id="longitude" name="longitude" type="number" step="0.0000001" min="-180" max="180" value="{{ old('longitude', $model?->longitude) }}" placeholder="{{ __('e.g. -118.2437') }}" />
            <x-admin.input-error :messages="$errors->get('longitude')" />
        </div>
    </div>
    <p class="mt-2 text-xs text-luxury-muted">{{ __('Reserved for future Google Places autocomplete integration — safe to leave blank for now.') }}</p>
</div>
