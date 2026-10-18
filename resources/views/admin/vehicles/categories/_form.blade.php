@php $category = $category ?? null; @endphp

<div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
    <div>
        <x-admin.input-label for="name" value="{{ __('Category Name') }}" />
        <x-admin.text-input id="name" name="name" type="text" value="{{ old('name', $category?->name) }}" placeholder="{{ __('e.g. Sedan') }}" required autofocus />
        <x-admin.input-error :messages="$errors->get('name')" />
    </div>

    <div>
        <x-admin.input-label for="description" value="{{ __('Description') }}" />
        <textarea id="description" name="description" rows="3" placeholder="{{ __('Optional short description shown to customers') }}"
            class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">{{ old('description', $category?->description) }}</textarea>
        <x-admin.input-error :messages="$errors->get('description')" />
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <div x-data="{ preview: '{{ $category?->icon_url }}' }">
            <x-admin.input-label value="{{ __('Icon') }}" />
            <div class="flex items-center gap-4">
                <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-full border border-luxury-border bg-luxury-graphite">
                    <template x-if="preview">
                        <img :src="preview" alt="{{ __('Icon preview') }}" class="h-full w-full object-cover">
                    </template>
                    <template x-if="!preview">
                        <span class="text-xs text-luxury-muted">{{ __('No icon') }}</span>
                    </template>
                </div>
                <label class="flex-1 cursor-pointer rounded-lg border border-dashed border-luxury-border px-4 py-3 text-center text-xs text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                    <span>{{ __('Click to upload icon') }}</span>
                    <input type="file" name="icon" accept="image/*" class="hidden"
                        @change="preview = $event.target.files.length ? URL.createObjectURL($event.target.files[0]) : preview">
                </label>
            </div>
            <p class="mt-2 text-xs text-luxury-muted">{{ __('Small square icon for lists. Max 512KB.') }}</p>
            <x-admin.input-error :messages="$errors->get('icon')" />
        </div>

        <div x-data="{ preview: '{{ $category?->image_url }}' }">
            <x-admin.input-label value="{{ __('Image') }}" />
            <div class="flex items-center gap-4">
                <div class="flex h-16 w-24 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-luxury-border bg-luxury-graphite">
                    <template x-if="preview">
                        <img :src="preview" alt="{{ __('Image preview') }}" class="h-full w-full object-cover">
                    </template>
                    <template x-if="!preview">
                        <span class="text-xs text-luxury-muted">{{ __('No image') }}</span>
                    </template>
                </div>
                <label class="flex-1 cursor-pointer rounded-lg border border-dashed border-luxury-border px-4 py-3 text-center text-xs text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                    <span>{{ __('Click to upload image') }}</span>
                    <input type="file" name="image" accept="image/*" class="hidden"
                        @change="preview = $event.target.files.length ? URL.createObjectURL($event.target.files[0]) : preview">
                </label>
            </div>
            <p class="mt-2 text-xs text-luxury-muted">{{ __('Showcase photo for this category. Max 2MB.') }}</p>
            <x-admin.input-error :messages="$errors->get('image')" />
        </div>
    </div>
</div>
