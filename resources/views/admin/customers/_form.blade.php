@php $customer = $customer ?? null; @endphp

<div class="space-y-6">
    <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
        <div x-data="{ preview: '{{ $customer?->avatar_url }}' }">
            <x-admin.input-label value="{{ __('Avatar') }}" />
            <div class="flex items-center gap-4">
                <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-full border border-luxury-border bg-luxury-graphite">
                    <template x-if="preview">
                        <img :src="preview" alt="{{ __('Avatar preview') }}" class="h-full w-full object-cover">
                    </template>
                    <template x-if="!preview">
                        <span class="text-xs text-luxury-muted">{{ __('No avatar') }}</span>
                    </template>
                </div>
                <label class="flex-1 cursor-pointer rounded-lg border border-dashed border-luxury-border px-4 py-3 text-center text-xs text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                    <span>{{ __('Click to upload avatar') }}</span>
                    <input type="file" name="avatar" accept="image/*" class="hidden"
                        @change="preview = $event.target.files.length ? URL.createObjectURL($event.target.files[0]) : preview">
                </label>
            </div>
            <p class="mt-2 text-xs text-luxury-muted">{{ __('Square photo recommended. Max 2MB.') }}</p>
            <x-admin.input-error :messages="$errors->get('avatar')" />
        </div>
    </div>

    <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
        <h3 class="text-sm font-semibold text-luxury-white">{{ __('Customer Information') }}</h3>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div>
                <x-admin.input-label for="name" value="{{ __('Full Name') }}" />
                <x-admin.text-input id="name" name="name" type="text" value="{{ old('name', $customer?->name) }}" required autofocus />
                <x-admin.input-error :messages="$errors->get('name')" />
            </div>

            <div>
                <x-admin.input-label for="email" value="{{ __('Email Address') }}" />
                <x-admin.text-input id="email" name="email" type="email" value="{{ old('email', $customer?->email) }}" required />
                <x-admin.input-error :messages="$errors->get('email')" />
            </div>

            <div>
                <x-admin.input-label for="phone" value="{{ __('Phone') }}" />
                <x-admin.text-input id="phone" name="phone" type="text" value="{{ old('phone', $customer?->phone) }}" />
                <x-admin.input-error :messages="$errors->get('phone')" />
            </div>
        </div>
    </div>
</div>
