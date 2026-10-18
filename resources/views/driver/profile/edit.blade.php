<x-driver.layouts.app :title="__('Profile Settings')">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Profile Settings') }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ __('Update your contact information and photo.') }}</p>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <form method="POST" action="{{ route('driver.profile.update') }}" enctype="multipart/form-data" class="space-y-6 lg:col-span-2">
            @csrf
            @method('PUT')

            {{-- Avatar hero — the driver's own photo/name/rating up front,
                 like an account-screen header, not buried in a form field. --}}
            <div class="flex items-center gap-4 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6" x-data="{ preview: '{{ $driver->photo_url }}' }">
                <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-full border border-luxury-border bg-luxury-graphite">
                    <template x-if="preview">
                        <img :src="preview" alt="{{ __('Photo preview') }}" class="h-full w-full object-cover">
                    </template>
                    <template x-if="!preview">
                        <x-icon name="user" class="h-8 w-8 text-luxury-muted" />
                    </template>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-base font-semibold text-luxury-white">{{ $driver->name }}</p>
                    @if ($driver->average_rating)
                        <p class="mt-0.5 flex items-center gap-1 text-xs text-luxury-muted">
                            <x-icon name="star" class="h-3.5 w-3.5 text-luxury-gold" /> {{ $driver->average_rating }}
                        </p>
                    @endif
                    <label class="tap-scale mt-2 inline-flex cursor-pointer items-center gap-1.5 text-xs font-medium text-luxury-gold hover:text-luxury-gold-light">
                        <x-icon name="pencil" class="h-3.5 w-3.5" />
                        <span>{{ __('Change Photo') }}</span>
                        <input type="file" name="photo" accept="image/*" class="hidden"
                            @change="preview = $event.target.files.length ? URL.createObjectURL($event.target.files[0]) : preview">
                    </label>
                </div>
            </div>
            <x-admin.input-error :messages="$errors->get('photo')" />

            <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
                <p class="text-xs font-semibold uppercase tracking-wider text-luxury-muted">{{ __('Contact Information') }}</p>

                <div>
                    <x-admin.input-label for="name" value="{{ __('Full Name') }}" />
                    <x-admin.text-input id="name" name="name" type="text" value="{{ old('name', $driver->name) }}" required />
                    <x-admin.input-error :messages="$errors->get('name')" />
                </div>

                <div>
                    <x-admin.input-label value="{{ __('Email Address') }}" />
                    <p class="w-full rounded-lg border border-luxury-border bg-luxury-graphite/40 px-4 py-3 text-sm text-luxury-muted">{{ $driver->email }}</p>
                    <p class="mt-1 text-xs text-luxury-muted">{{ __('Contact the administrator to change your email address.') }}</p>
                </div>

                <div>
                    <x-admin.input-label for="phone" value="{{ __('Phone') }}" />
                    <x-admin.text-input id="phone" name="phone" type="text" value="{{ old('phone', $driver->phone) }}" />
                    <x-admin.input-error :messages="$errors->get('phone')" />
                </div>

                <div>
                    <x-admin.input-label for="address" value="{{ __('Address') }}" />
                    <textarea id="address" name="address" rows="2"
                        class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">{{ old('address', $driver->address) }}</textarea>
                    <x-admin.input-error :messages="$errors->get('address')" />
                </div>
            </div>

            <x-admin.button type="submit" variant="primary" class="w-full sm:w-auto">
                {{ __('Save Changes') }}
            </x-admin.button>
        </form>

        {{-- Vehicle — read-only, assigned by the admin; shown here so it's
             actually visible somewhere in the driver panel at all. --}}
        <div class="space-y-4">
            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
                <p class="text-xs font-semibold uppercase tracking-wider text-luxury-muted">{{ __('Your Vehicle') }}</p>
                @if ($driver->vehicle)
                    <div class="mt-4 flex items-center gap-3">
                        <div class="h-12 w-12 shrink-0 overflow-hidden rounded-lg border border-luxury-border bg-luxury-graphite">
                            @if ($driver->vehicle->image_url)
                                <x-lazy-image :src="$driver->vehicle->image_url" :alt="$driver->vehicle->name" />
                            @else
                                <div class="flex h-full w-full items-center justify-center">
                                    <x-icon name="car" class="h-5 w-5 text-luxury-muted" />
                                </div>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-luxury-white">{{ $driver->vehicle->category?->name ?? $driver->vehicle->name }}</p>
                            @if ($driver->vehicle->plate_number)
                                <p class="text-xs uppercase tracking-wide text-luxury-muted">{{ $driver->vehicle->plate_number }}</p>
                            @endif
                        </div>
                    </div>
                @else
                    <p class="mt-3 text-sm text-luxury-muted">{{ __('No vehicle assigned yet — contact the administrator.') }}</p>
                @endif
            </div>

            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
                <p class="text-xs font-semibold uppercase tracking-wider text-luxury-muted">{{ __('Status') }}</p>
                <div class="mt-3 flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full {{ $driver->is_online ? 'bg-emerald-400' : 'bg-luxury-muted' }}"></span>
                    <span class="text-sm font-medium text-luxury-white">{{ $driver->is_online ? __('Online') : __('Offline') }}</span>
                </div>
                <p class="mt-1 text-xs text-luxury-muted">{{ __('Commission rate: :rate%', ['rate' => (float) $driver->commission_rate]) }}</p>
            </div>
        </div>
    </div>
</x-driver.layouts.app>
