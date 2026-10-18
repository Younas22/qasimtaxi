<x-customer.layouts.guest :title="__('Verify Email')">
    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-luxury-gold/10 text-luxury-gold">
        <x-icon name="mail" class="h-7 w-7" />
    </span>

    <h1 class="mb-1 mt-5 text-center text-xl font-semibold text-luxury-white">{{ __('Verify your email') }}</h1>
    <p class="mb-6 text-center text-sm text-luxury-muted">
        {{ __("Thanks for signing up! Please verify your email address by clicking the link we sent you.") }}
    </p>

    @if (session('status') === 'verification-link-sent')
        <div class="mb-6 rounded-lg border border-luxury-gold/30 bg-luxury-gold/10 px-4 py-3 text-center text-sm text-luxury-gold">
            {{ __('A new verification link has been sent to your email address.') }}
        </div>
    @elseif (session('status'))
        <div class="mb-6 rounded-lg border border-luxury-gold/30 bg-luxury-gold/10 px-4 py-3 text-center text-sm text-luxury-gold">
            {{ session('status') }}
        </div>
    @endif

    <div class="flex flex-col gap-3">
        <form method="POST" action="{{ route('customer.verification.resend') }}">
            @csrf
            <x-admin.button type="submit" variant="primary" class="w-full">
                {{ __('Resend Verification Email') }}
            </x-admin.button>
        </form>

        <form method="POST" action="{{ route('customer.logout') }}">
            @csrf
            <button type="submit" class="w-full text-center text-sm text-luxury-muted hover:text-luxury-white">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-customer.layouts.guest>
