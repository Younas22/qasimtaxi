@php $routeType = $routeType ?? null; @endphp

<div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
    <div>
        <x-admin.input-label for="name" :value="__('Route Type Name')" />
        <x-admin.text-input id="name" name="name" type="text" value="{{ old('name', $routeType?->name) }}" :placeholder="__('e.g. Airport Route')" required autofocus />
        <x-admin.input-error :messages="$errors->get('name')" />
    </div>

    <div>
        <x-admin.input-label for="description" :value="__('Description')" />
        <textarea id="description" name="description" rows="3" placeholder="{{ __('Optional short description') }}"
            class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">{{ old('description', $routeType?->description) }}</textarea>
        <x-admin.input-error :messages="$errors->get('description')" />
    </div>
</div>
