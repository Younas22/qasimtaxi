@php $driver = $driver ?? null; @endphp

<div class="space-y-6">
    {{-- Photo --}}
    <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
        <div x-data="{ preview: '{{ $driver?->photo_url }}' }">
            <x-admin.input-label value="{{ __('Driver Photo') }}" />
            <div class="flex items-center gap-4">
                <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-full border border-luxury-border bg-luxury-graphite">
                    <template x-if="preview">
                        <img :src="preview" alt="{{ __('Photo preview') }}" class="h-full w-full object-cover">
                    </template>
                    <template x-if="!preview">
                        <span class="text-xs text-luxury-muted">{{ __('No photo') }}</span>
                    </template>
                </div>
                <label class="flex-1 cursor-pointer rounded-lg border border-dashed border-luxury-border px-4 py-3 text-center text-xs text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                    <span>{{ __('Click to upload photo') }}</span>
                    <input type="file" name="photo" accept="image/*" class="hidden"
                        @change="preview = $event.target.files.length ? URL.createObjectURL($event.target.files[0]) : preview">
                </label>
            </div>
            <p class="mt-2 text-xs text-luxury-muted">{{ __('Square photo recommended. Max 2MB.') }}</p>
            <x-admin.input-error :messages="$errors->get('photo')" />
        </div>
    </div>

    {{-- Personal Information --}}
    <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
        <h3 class="text-sm font-semibold text-luxury-white">{{ __('Personal Information') }}</h3>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div>
                <x-admin.input-label for="name" value="{{ __('Full Name') }}" />
                <x-admin.text-input id="name" name="name" type="text" value="{{ old('name', $driver?->name) }}" required autofocus />
                <x-admin.input-error :messages="$errors->get('name')" />
            </div>

            <div>
                <x-admin.input-label for="email" value="{{ __('Email Address') }}" />
                <x-admin.text-input id="email" name="email" type="email" value="{{ old('email', $driver?->email) }}" required />
                <x-admin.input-error :messages="$errors->get('email')" />
            </div>

            <div>
                <x-admin.input-label for="phone" value="{{ __('Phone') }}" />
                <x-admin.text-input id="phone" name="phone" type="text" value="{{ old('phone', $driver?->phone) }}" />
                <x-admin.input-error :messages="$errors->get('phone')" />
            </div>

            <div class="sm:col-span-2">
                <x-admin.input-label for="address" value="{{ __('Address') }}" />
                <textarea id="address" name="address" rows="2"
                    class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">{{ old('address', $driver?->address) }}</textarea>
                <x-admin.input-error :messages="$errors->get('address')" />
            </div>
        </div>
    </div>

    {{-- Documents --}}
    <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
        <h3 class="text-sm font-semibold text-luxury-white">{{ __('Documents') }}</h3>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
            <div>
                <x-admin.input-label for="license_number" value="{{ __('License Number') }}" />
                <x-admin.text-input id="license_number" name="license_number" type="text" value="{{ old('license_number', $driver?->license_number) }}" />
                <x-admin.input-error :messages="$errors->get('license_number')" />
            </div>

            <div>
                <x-admin.input-label for="passport_number" value="{{ __('Passport Number') }}" />
                <x-admin.text-input id="passport_number" name="passport_number" type="text" value="{{ old('passport_number', $driver?->passport_number) }}" />
                <x-admin.input-error :messages="$errors->get('passport_number')" />
            </div>

            <div>
                <x-admin.input-label for="national_id" value="{{ __('National ID') }}" />
                <x-admin.text-input id="national_id" name="national_id" type="text" value="{{ old('national_id', $driver?->national_id) }}" />
                <x-admin.input-error :messages="$errors->get('national_id')" />
            </div>
        </div>
    </div>

    {{-- Assignment & Commission --}}
    <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
        <h3 class="text-sm font-semibold text-luxury-white">{{ __('Assignment & Commission') }}</h3>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div>
                <x-admin.input-label for="vehicle_id" value="{{ __('Assigned Vehicle') }}" />
                <select id="vehicle_id" name="vehicle_id"
                    class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
                    <option value="">{{ __('Unassigned') }}</option>
                    @foreach ($vehicles as $v)
                        <option value="{{ $v->id }}" @selected(old('vehicle_id', $driver?->vehicle_id) == $v->id)>{{ $v->name }} ({{ $v->plate_number ?? __('no plate') }})</option>
                    @endforeach
                </select>
                <x-admin.input-error :messages="$errors->get('vehicle_id')" />
            </div>

            <div>
                <x-admin.input-label for="commission_rate" value="{{ __('Commission Rate (%)') }}" />
                <x-admin.text-input id="commission_rate" name="commission_rate" type="number" step="0.01" min="0" max="100" value="{{ old('commission_rate', $driver?->commission_rate ?? 20) }}" required />
                <x-admin.input-error :messages="$errors->get('commission_rate')" />
            </div>
        </div>
    </div>

    {{-- Login Access --}}
    <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
        <h3 class="text-sm font-semibold text-luxury-white">{{ __('Login Access') }}</h3>
        <p class="text-xs text-luxury-muted">
            {{ $driver?->password
                ? __('Leave blank to keep the current password.')
                : __('Set a password to give this driver access to the Driver Panel. Leave blank for no login access yet.') }}
        </p>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div>
                <x-admin.input-label for="password" value="{{ __('Password') }}" />
                <x-admin.text-input id="password" name="password" type="password" autocomplete="new-password" />
                <x-admin.input-error :messages="$errors->get('password')" />
            </div>

            <div>
                <x-admin.input-label for="password_confirmation" value="{{ __('Confirm Password') }}" />
                <x-admin.text-input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" />
            </div>
        </div>
    </div>
</div>
