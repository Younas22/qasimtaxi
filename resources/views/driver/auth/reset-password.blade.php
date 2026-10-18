<x-customer.layouts.guest :title="__('Reset Password')">
    <h1 class="mb-1 text-xl font-semibold text-luxury-white">{{ __('Reset your password') }}</h1>
    <p class="mb-6 text-sm text-luxury-muted">{{ __('Choose a new password for your account.') }}</p>

    <form method="POST" action="{{ route('driver.password.store') }}" class="space-y-5">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <x-admin.input-label for="email" :value="__('Email Address')" />
            <x-admin.text-input id="email" name="email" type="email" value="{{ old('email', $email) }}" required autofocus />
            <x-admin.input-error :messages="$errors->get('email')" />
        </div>

        <div>
            <x-admin.input-label for="password" :value="__('New Password')" />
            <x-admin.text-input id="password" name="password" type="password" required autocomplete="new-password" />
            <x-admin.input-error :messages="$errors->get('password')" />
        </div>

        <div>
            <x-admin.input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-admin.text-input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" />
            <x-admin.input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <x-admin.button type="submit" variant="primary" class="w-full">
            {{ __('Reset Password') }}
        </x-admin.button>
    </form>
</x-customer.layouts.guest>
