<x-customer.layouts.guest :title="__('Create Account')">
    <h1 class="mb-1 text-xl font-semibold text-luxury-white">{{ __('Create your account') }}</h1>
    <p class="mb-6 text-sm text-luxury-muted">{{ __('Book luxury rides in minutes.') }}</p>

    <form method="POST" action="{{ route('customer.register') }}" class="space-y-5">
        @csrf

        <div>
            <x-admin.input-label for="name" :value="__('Full Name')" />
            <x-admin.text-input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name" />
            <x-admin.input-error :messages="$errors->get('name')" />
        </div>

        <div>
            <x-admin.input-label for="email" :value="__('Email Address')" />
            <x-admin.text-input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username" />
            <x-admin.input-error :messages="$errors->get('email')" />
        </div>

        <div>
            <x-admin.input-label for="phone" :value="__('Phone Number')" />
            <x-admin.text-input id="phone" name="phone" type="tel" value="{{ old('phone') }}" autocomplete="tel" />
            <x-admin.input-error :messages="$errors->get('phone')" />
        </div>

        <div>
            <x-admin.input-label for="password" :value="__('Password')" />
            <x-admin.text-input id="password" name="password" type="password" required autocomplete="new-password" />
            <x-admin.input-error :messages="$errors->get('password')" />
        </div>

        <div>
            <x-admin.input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-admin.text-input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" />
            <x-admin.input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <x-admin.button type="submit" variant="primary" class="w-full">
            {{ __('Create Account') }}
        </x-admin.button>

        <p class="text-center text-sm text-luxury-muted">
            {{ __('Already have an account?') }}
            <a href="{{ route('customer.login') }}" class="font-medium text-luxury-gold hover:text-luxury-gold-light">{{ __('Sign in') }}</a>
        </p>
    </form>
</x-customer.layouts.guest>
