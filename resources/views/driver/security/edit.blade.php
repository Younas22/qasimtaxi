<x-driver.layouts.app :title="__('Security')">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Security') }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ __('Manage your account password.') }}</p>
    </div>

    <div class="max-w-2xl">
        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
            <h3 class="mb-4 text-sm font-semibold text-luxury-white">{{ __('Change Password') }}</h3>
            <form method="POST" action="{{ route('driver.security.update') }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <x-admin.input-label for="current_password" value="{{ __('Current Password') }}" />
                    <x-admin.text-input id="current_password" name="current_password" type="password" required autocomplete="current-password" />
                    <x-admin.input-error :messages="$errors->get('current_password')" />
                </div>

                <div>
                    <x-admin.input-label for="password" value="{{ __('New Password') }}" />
                    <x-admin.text-input id="password" name="password" type="password" required autocomplete="new-password" />
                    <x-admin.input-error :messages="$errors->get('password')" />
                </div>

                <div>
                    <x-admin.input-label for="password_confirmation" value="{{ __('Confirm New Password') }}" />
                    <x-admin.text-input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" />
                    <x-admin.input-error :messages="$errors->get('password_confirmation')" />
                </div>

                <x-admin.button type="submit" variant="primary" class="w-full sm:w-auto">{{ __('Update Password') }}</x-admin.button>
            </form>
        </div>
    </div>
</x-driver.layouts.app>
