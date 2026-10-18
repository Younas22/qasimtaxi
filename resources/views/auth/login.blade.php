<x-customer.layouts.guest :title="__('Sign In')">
    <h1 class="mb-1 text-xl font-semibold text-luxury-white">{{ __('Welcome back') }}</h1>
    <p class="mb-6 text-sm text-luxury-muted">{{ __('Sign in to your account.') }}</p>

    @if (session('status'))
        <div class="mb-6 rounded-lg border border-luxury-gold/30 bg-luxury-gold/10 px-4 py-3 text-sm text-luxury-gold">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <x-admin.input-label for="email" :value="__('Email Address')" />
            <x-admin.text-input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" />
            <x-admin.input-error :messages="$errors->get('email')" />
        </div>

        <div>
            <div class="flex items-center justify-between">
                <x-admin.input-label for="password" :value="__('Password')" class="!mb-0" />
                <a href="{{ route('customer.password.request') }}" class="text-xs text-luxury-gold hover:text-luxury-gold-light">
                    {{ __('Forgot password?') }}
                </a>
            </div>
            <div class="mt-2">
                <x-admin.text-input id="password" name="password" type="password" required autocomplete="current-password" />
            </div>
            <x-admin.input-error :messages="$errors->get('password')" />
        </div>

        <label class="flex items-center gap-2 text-sm text-luxury-muted">
            <input type="checkbox" name="remember" class="rounded border-luxury-border bg-luxury-charcoal text-luxury-gold focus:ring-luxury-gold">
            {{ __('Remember me') }}
        </label>

        <x-admin.button type="submit" variant="primary" class="w-full">
            {{ __('Sign In') }}
        </x-admin.button>

        <p class="text-center text-sm text-luxury-muted">
            {{ __("Don't have an account?") }}
            <a href="{{ route('customer.register') }}" class="font-medium text-luxury-gold hover:text-luxury-gold-light">{{ __('Create one') }}</a>
        </p>
    </form>
</x-customer.layouts.guest>
