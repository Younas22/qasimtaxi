@php $model = $model ?? null; @endphp

<div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6"
    x-data="{
        allStates: {{ \Illuminate\Support\Js::from($states->map(fn ($s) => ['id' => $s->id, 'name' => $s->name, 'country_id' => $s->country_id])->values()) }},
        country: {{ old('country_id', $model?->state?->country_id) ?: 'null' }},
        state: {{ old('state_id', $model?->state_id) ?: 'null' }},
    }">
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <div>
            <x-admin.input-label value="{{ __('Country') }}" />
            <select x-model.number="country" @change="state = null"
                class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
                <option value="">{{ __('Select a country') }}</option>
                @foreach ($countries as $countryOption)
                    <option value="{{ $countryOption->id }}">{{ $countryOption->name }}</option>
                @endforeach
            </select>
            <p class="mt-2 text-xs text-luxury-muted">{{ __('Filters the state list.') }}</p>
        </div>

        <div>
            <x-admin.input-label for="state_id" value="{{ __('State') }}" />
            <select id="state_id" name="state_id" x-model.number="state" required :disabled="!country"
                class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition disabled:opacity-50">
                <option value="">{{ __('Select a state') }}</option>
                <template x-for="s in allStates.filter(s => s.country_id === country)" :key="s.id">
                    <option :value="s.id" x-text="s.name"></option>
                </template>
            </select>
            <x-admin.input-error :messages="$errors->get('state_id')" />
        </div>

        <div class="sm:col-span-2">
            <x-admin.input-label for="name" value="{{ __('City Name') }}" />
            <x-admin.text-input id="name" name="name" type="text" value="{{ old('name', $model?->name) }}" placeholder="{{ __('e.g. Los Angeles') }}" required autofocus />
            <x-admin.input-error :messages="$errors->get('name')" />
        </div>
    </div>

    <x-admin.google-fields :model="$model" />
</div>
