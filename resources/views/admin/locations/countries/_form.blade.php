@php $model = $model ?? null; @endphp

<div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <div>
            <x-admin.input-label for="name" value="{{ __('Country Name') }}" />
            <x-admin.text-input id="name" name="name" type="text" value="{{ old('name', $model?->name) }}" placeholder="{{ __('e.g. United States') }}" required autofocus />
            <x-admin.input-error :messages="$errors->get('name')" />
        </div>

        <div>
            <x-admin.input-label for="code" value="{{ __('Country Code (ISO 2)') }}" />
            <x-admin.text-input id="code" name="code" type="text" value="{{ old('code', $model?->code) }}" placeholder="{{ __('e.g. US') }}" maxlength="2" class="uppercase" required />
            <x-admin.input-error :messages="$errors->get('code')" />
        </div>
    </div>

    <x-admin.google-fields :model="$model" />
</div>
