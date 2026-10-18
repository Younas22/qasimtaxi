<x-admin.layouts.app :title="__('My Profile')">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('My Profile') }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ __('Update your personal information and profile photo.') }}</p>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Edit profile --}}
        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6 lg:col-span-2">
            <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PUT')

                <div x-data="{ preview: '{{ $admin->avatar_url }}' }">
                    <x-admin.input-label :value="__('Profile Picture')" />
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
                    <div class="sm:col-span-2">
                        <x-admin.input-label for="name" :value="__('Name')" />
                        <x-admin.text-input id="name" name="name" type="text" value="{{ old('name', $admin->name) }}" required autocomplete="name" />
                        <x-admin.input-error :messages="$errors->get('name')" />
                    </div>

                    <div>
                        <x-admin.input-label for="email" :value="__('Email Address')" />
                        <x-admin.text-input id="email" name="email" type="email" value="{{ old('email', $admin->email) }}" required autocomplete="email" />
                        <x-admin.input-error :messages="$errors->get('email')" />
                    </div>

                    <div>
                        <x-admin.input-label for="phone" :value="__('Phone Number')" />
                        <x-admin.text-input id="phone" name="phone" type="tel" value="{{ old('phone', $admin->phone) }}" autocomplete="tel" />
                        <x-admin.input-error :messages="$errors->get('phone')" />
                    </div>
                </div>

                <x-admin.button type="submit" variant="primary">{{ __('Save Changes') }}</x-admin.button>
            </form>
        </div>

        {{-- Change password --}}
        <div class="space-y-4">
            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6" x-data="{ open: false }">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-luxury-gold/10 text-luxury-gold">
                        <x-icon name="lock" class="h-5 w-5" />
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-luxury-white">{{ __('Password') }}</p>
                        <p class="text-xs text-luxury-muted">{{ __('Keep your account secure.') }}</p>
                    </div>
                </div>

                <button type="button" @click="open = !open"
                    class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-lg border border-luxury-border px-4 py-2.5 text-sm font-medium text-luxury-white transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                    {{ __('Change Password') }}
                </button>

                <form method="POST" action="{{ route('admin.profile.password') }}" x-show="open" x-cloak class="mt-4 space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-admin.input-label for="current_password" :value="__('Current Password')" />
                        <x-admin.text-input id="current_password" name="current_password" type="password" autocomplete="current-password" />
                        <x-admin.input-error :messages="$errors->get('current_password')" />
                    </div>

                    <div>
                        <x-admin.input-label for="password" :value="__('New Password')" />
                        <x-admin.text-input id="password" name="password" type="password" autocomplete="new-password" />
                        <x-admin.input-error :messages="$errors->get('password')" />
                    </div>

                    <div>
                        <x-admin.input-label for="password_confirmation" :value="__('Confirm New Password')" />
                        <x-admin.text-input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" />
                    </div>

                    <x-admin.button type="submit" variant="primary">{{ __('Update Password') }}</x-admin.button>
                </form>
            </div>

            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
                <p class="text-xs uppercase tracking-wide text-luxury-muted">{{ __('Member Since') }}</p>
                <p class="mt-1 text-sm font-medium text-luxury-white">{{ $admin->created_at->format('M d, Y') }}</p>
            </div>
        </div>
    </div>
</x-admin.layouts.app>
