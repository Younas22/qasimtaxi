<x-admin.layouts.guest :title="__('Forgot Password')">
    <h1 class="mb-1 text-xl font-semibold text-luxury-white">{{ __('Forgot your password?') }}</h1>
    <p class="mb-6 text-sm text-luxury-muted">{{ __("Enter your email and we'll send you a reset link.") }}</p>

    @if (session('status'))
        <div class="mb-6 rounded-lg border border-luxury-gold/30 bg-luxury-gold/10 px-4 py-3 text-sm text-luxury-gold">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.password.email') }}" class="space-y-5">
        @csrf

        <div>
            <x-admin.input-label for="email" :value="__('Email Address')" />
            <x-admin.text-input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus />
            <x-admin.input-error :messages="$errors->get('email')" />
        </div>

        <x-admin.button type="submit" variant="primary" class="w-full">
            {{ __('Email Reset Link') }}
        </x-admin.button>

        <a href="{{ route('admin.login') }}" class="block text-center text-sm text-luxury-muted hover:text-luxury-white">
            {{ __('Back to sign in') }}
        </a>
    </form>
</x-admin.layouts.guest>
