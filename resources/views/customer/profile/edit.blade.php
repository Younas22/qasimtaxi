<x-customer.layouts.app :title="__('Profile Settings')">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Profile Settings') }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ __('Update your personal information and profile photo.') }}</p>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Edit profile --}}
        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6 lg:col-span-2">
            <form method="POST" action="{{ route('customer.profile.update') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PUT')

                <div x-data="{ preview: '{{ $customer->avatar_url }}' }">
                    <x-admin.input-label value="Profile Picture" />
                    <div class="flex items-center gap-4">
                        <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-full border border-luxury-border bg-luxury-graphite">
                            <template x-if="preview">
                                <img :src="preview" alt="{{ __('Avatar preview') }}" class="h-full w-full object-cover">
                            </template>
                            <template x-if="!preview">
                                <span class="text-xs text-luxury-muted">{{ __('No photo') }}</span>
                            </template>
                        </div>
                        <label class="flex-1 cursor-pointer rounded-lg border border-dashed border-luxury-border px-4 py-3 text-center text-xs text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                            <span>{{ __('Change Photo') }}</span>
                            <input type="file" name="avatar" accept="image/*" class="hidden"
                                @change="preview = $event.target.files.length ? URL.createObjectURL($event.target.files[0]) : preview">
                        </label>
                    </div>
                    <p class="mt-2 text-xs text-luxury-muted">{{ __('JPG or PNG. Max 2MB.') }}</p>
                    <x-admin.input-error :messages="$errors->get('avatar')" />
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <x-admin.input-label for="first_name" value="First Name" />
                        <x-admin.text-input id="first_name" name="first_name" type="text" value="{{ old('first_name', $customer->first_name) }}" required autocomplete="given-name" />
                        <x-admin.input-error :messages="$errors->get('first_name')" />
                    </div>

                    <div>
                        <x-admin.input-label for="last_name" value="Last Name" />
                        <x-admin.text-input id="last_name" name="last_name" type="text" value="{{ old('last_name', $customer->last_name) }}" required autocomplete="family-name" />
                        <x-admin.input-error :messages="$errors->get('last_name')" />
                    </div>

                    <div class="sm:col-span-2">
                        <x-admin.input-label for="email" value="Email Address" />
                        <x-admin.text-input id="email" name="email" type="email" value="{{ old('email', $customer->email) }}" required autocomplete="email" />
                        <x-admin.input-error :messages="$errors->get('email')" />
                    </div>

                    <div>
                        <x-admin.input-label for="phone" value="Phone Number" />
                        <x-admin.text-input id="phone" name="phone" type="tel" value="{{ old('phone', $customer->phone) }}" autocomplete="tel" />
                        <x-admin.input-error :messages="$errors->get('phone')" />
                    </div>

                    <div>
                        <x-admin.input-label for="date_of_birth" value="Date of Birth" />
                        <x-admin.text-input id="date_of_birth" name="date_of_birth" type="date"
                            value="{{ old('date_of_birth', $customer->date_of_birth?->format('Y-m-d')) }}" max="{{ now()->subDay()->format('Y-m-d') }}" />
                        <x-admin.input-error :messages="$errors->get('date_of_birth')" />
                    </div>

                    <div class="sm:col-span-2">
                        <x-admin.input-label for="gender" value="Gender" />
                        <select id="gender" name="gender"
                            class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
                            <option value="">{{ __('Prefer not to say') }}</option>
                            @foreach (\App\Models\Customer::GENDERS as $value => $label)
                                <option value="{{ $value }}" @selected(old('gender', $customer->gender) === $value)>{{ __($label) }}</option>
                            @endforeach
                        </select>
                        <x-admin.input-error :messages="$errors->get('gender')" />
                    </div>
                </div>

                <x-admin.button type="submit" variant="primary">{{ __('Save Changes') }}</x-admin.button>
            </form>
        </div>

        {{-- Change password --}}
        <div class="space-y-4">
            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-luxury-gold/10 text-luxury-gold">
                        <x-icon name="lock" class="h-5 w-5" />
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-luxury-white">{{ __('Password') }}</p>
                        <p class="text-xs text-luxury-muted">{{ __('Keep your account secure.') }}</p>
                    </div>
                </div>
                <a href="{{ route('customer.security.edit') }}"
                    class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-lg border border-luxury-border px-4 py-2.5 text-sm font-medium text-luxury-white transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                    {{ __('Change Password') }}
                </a>
            </div>

            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
                <p class="text-xs uppercase tracking-wide text-luxury-muted">{{ __('Member Since') }}</p>
                <p class="mt-1 text-sm font-medium text-luxury-white">{{ $customer->created_at->format('M d, Y') }}</p>
            </div>
        </div>
    </div>
</x-customer.layouts.app>
