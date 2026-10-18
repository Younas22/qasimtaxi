@php $promotion = $promotion ?? null; @endphp

<div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <div>
            <x-admin.input-label for="title" value="Title" />
            <x-admin.text-input id="title" name="title" type="text" value="{{ old('title', $promotion?->title) }}" placeholder="e.g. 20% Off Your First Ride" required autofocus />
            <x-admin.input-error :messages="$errors->get('title')" />
        </div>

        <div>
            <x-admin.input-label for="badge_text" value="Badge Text (optional)" />
            <x-admin.text-input id="badge_text" name="badge_text" type="text" value="{{ old('badge_text', $promotion?->badge_text) }}" placeholder="e.g. Limited Time" />
            <x-admin.input-error :messages="$errors->get('badge_text')" />
        </div>
    </div>

    <div>
        <x-admin.input-label for="subtitle" value="Subtitle" />
        <textarea id="subtitle" name="subtitle" rows="2" placeholder="Short supporting text"
            class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">{{ old('subtitle', $promotion?->subtitle) }}</textarea>
        <x-admin.input-error :messages="$errors->get('subtitle')" />
    </div>

    <div>
        <x-admin.input-label for="link_url" value="Link URL (optional)" />
        <x-admin.text-input id="link_url" name="link_url" type="text" value="{{ old('link_url', $promotion?->link_url) }}" placeholder="e.g. /services or https://..." />
        <x-admin.input-error :messages="$errors->get('link_url')" />
    </div>

    <div x-data="{ preview: '{{ $promotion?->image_url }}' }">
        <x-admin.input-label value="Banner Image" />
        <div class="flex items-center gap-4">
            <div class="flex h-16 w-24 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-luxury-border bg-luxury-graphite">
                <template x-if="preview">
                    <img :src="preview" alt="Image preview" class="h-full w-full object-cover">
                </template>
                <template x-if="!preview">
                    <span class="text-xs text-luxury-muted">No image</span>
                </template>
            </div>
            <label class="flex-1 cursor-pointer rounded-lg border border-dashed border-luxury-border px-4 py-3 text-center text-xs text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                <span>Click to upload image</span>
                <input type="file" name="image" accept="image/*" class="hidden"
                    @change="preview = $event.target.files.length ? URL.createObjectURL($event.target.files[0]) : preview">
            </label>
        </div>
        <p class="mt-2 text-xs text-luxury-muted">Promo banner image. Max 2MB.</p>
        <x-admin.input-error :messages="$errors->get('image')" />
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
        <div>
            <x-admin.input-label for="starts_at" value="Starts At (optional)" />
            <x-admin.text-input id="starts_at" name="starts_at" type="date" value="{{ old('starts_at', $promotion?->starts_at?->format('Y-m-d')) }}" />
            <x-admin.input-error :messages="$errors->get('starts_at')" />
        </div>

        <div>
            <x-admin.input-label for="ends_at" value="Ends At (optional)" />
            <x-admin.text-input id="ends_at" name="ends_at" type="date" value="{{ old('ends_at', $promotion?->ends_at?->format('Y-m-d')) }}" />
            <x-admin.input-error :messages="$errors->get('ends_at')" />
        </div>

        <div>
            <x-admin.input-label for="sort_order" value="Sort Order" />
            <x-admin.text-input id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $promotion?->sort_order ?? 0) }}" />
            <x-admin.input-error :messages="$errors->get('sort_order')" />
        </div>
    </div>

    <label class="flex items-center gap-2">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $promotion?->is_active ?? true)) class="h-4 w-4 rounded border-luxury-border bg-luxury-charcoal text-luxury-gold focus:ring-1 focus:ring-luxury-gold">
        <span class="text-sm text-luxury-white">Active</span>
    </label>
</div>
