@php $area = $area ?? null; @endphp

<div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
    <div>
        <x-admin.input-label for="name" :value="__('Area Name')" />
        <x-admin.text-input id="name" name="name" type="text" value="{{ old('name', $area?->name) }}" :placeholder="__('e.g. Hasselt')" required autofocus />
        <x-admin.input-error :messages="$errors->get('name')" />
    </div>

    <div>
        <x-admin.input-label for="description" :value="__('Description')" />
        <textarea id="description" name="description" rows="4" placeholder="{{ __('Optional — shown on this area\'s own detail page. Leave blank to use a default sentence.') }}"
            class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">{{ old('description', $area?->description) }}</textarea>
        <p class="mt-1.5 text-xs text-luxury-muted">{{ __('Also shown as a short snippet in the areas list above. Write something specific to this town — a page that only swaps the town name into a generic sentence is exactly what search engines penalize.') }}</p>
        <x-admin.input-error :messages="$errors->get('description')" />
    </div>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <div>
            <x-admin.input-label for="meta_title" :value="__('SEO Title (optional)')" />
            <x-admin.text-input id="meta_title" name="meta_title" type="text" value="{{ old('meta_title', $area?->meta_title) }}" :placeholder="__('Taxi Service in :area', ['area' => $area?->name ?: 'Hasselt'])" />
            <p class="mt-1.5 text-xs text-luxury-muted">{{ __('Leave blank to use the default heading for this area.') }}</p>
            <x-admin.input-error :messages="$errors->get('meta_title')" />
        </div>

        <div>
            <x-admin.input-label for="canonical_override" :value="__('Canonical URL (optional)')" />
            <x-admin.text-input id="canonical_override" name="canonical_override" type="url" value="{{ old('canonical_override', $area?->canonical_override) }}" />
            <x-admin.input-error :messages="$errors->get('canonical_override')" />
        </div>

        <div class="sm:col-span-2">
            <x-admin.input-label for="meta_description" :value="__('Meta Description (optional)')" />
            <textarea id="meta_description" name="meta_description" rows="2" maxlength="500"
                class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">{{ old('meta_description', $area?->meta_description) }}</textarea>
            <p class="mt-1.5 text-xs text-luxury-muted">{{ __("Leave blank to fall back to the description above.") }}</p>
            <x-admin.input-error :messages="$errors->get('meta_description')" />
        </div>

        <div class="sm:col-span-2" x-data="{ preview: '{{ $area?->og_image_url }}' }">
            <x-admin.input-label :value="__('Social Share Image (optional)')" />
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-24 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-luxury-border bg-luxury-graphite">
                    <template x-if="preview"><img :src="preview" alt="" class="h-full w-full object-cover"></template>
                    <template x-if="!preview"><span class="text-[10px] text-luxury-muted">{{ __('No image') }}</span></template>
                </div>
                <label class="flex-1 cursor-pointer rounded-lg border border-dashed border-luxury-border px-4 py-3 text-center text-xs text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                    <span>{{ __('Click to upload image') }}</span>
                    <input type="file" name="og_image" accept="image/*" class="hidden" @change="preview = $event.target.files.length ? URL.createObjectURL($event.target.files[0]) : preview">
                </label>
            </div>
            <x-admin.input-error :messages="$errors->get('og_image')" />
        </div>

        <div class="sm:col-span-2 grid grid-cols-1 gap-3 sm:grid-cols-2">
            <x-admin.toggle name="robots_index" :checked="old('robots_index', $area?->robots_index ?? true)"
                label="{{ __('Allow search engines to index this area page') }}"
                description="{{ __('Turn this off while you\'re still filling in the description above, then switch it on once the page is genuinely ready.') }}" />
            <x-admin.toggle name="robots_follow" :checked="old('robots_follow', $area?->robots_follow ?? true)"
                label="{{ __('Allow search engines to follow links on this page') }}" />
        </div>
    </div>
</div>
