<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ \App\Models\Language::findActiveByCode(app()->getLocale())?->direction ?? 'ltr' }}" data-theme="dark" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, follow">
    <title>{{ __('Page Not Found') }} — {{ setting('company_name', config('app.name', 'Limo Schedule')) }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('admin.partials.theme-vars')
</head>
<body class="flex min-h-screen items-center justify-center bg-luxury-black px-4 font-sans text-luxury-white antialiased">
    <div class="pointer-events-none fixed inset-0 overflow-hidden">
        <div class="absolute left-1/2 top-0 h-96 w-96 -translate-x-1/2 rounded-full bg-luxury-gold/10 blur-3xl"></div>
    </div>

    <div class="relative w-full max-w-md text-center">
        <p class="text-6xl font-semibold text-luxury-gold">404</p>
        <h1 class="mt-4 text-xl font-semibold text-luxury-white">{{ __("This page doesn't exist") }}</h1>
        <p class="mt-2 text-sm text-luxury-muted">
            {{ __("The page you're looking for may have moved or no longer exists. Here are a few places to start instead:") }}
        </p>

        <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
            @if (\Illuminate\Support\Facades\Route::has('pages.home'))
                <a href="{{ route('pages.home') }}" class="inline-flex items-center justify-center rounded-lg bg-luxury-gold px-5 py-3 text-sm font-semibold text-luxury-black transition hover:bg-luxury-gold-light">
                    {{ __('Home') }}
                </a>
            @endif
            @if (\Illuminate\Support\Facades\Route::has('pages.show'))
                <a href="{{ route('pages.show', 'services') }}" class="inline-flex items-center justify-center rounded-lg border border-luxury-border px-5 py-3 text-sm font-medium text-luxury-white transition hover:border-luxury-gold/40">
                    {{ __('Services') }}
                </a>
                <a href="{{ route('pages.show', 'areas') }}" class="inline-flex items-center justify-center rounded-lg border border-luxury-border px-5 py-3 text-sm font-medium text-luxury-white transition hover:border-luxury-gold/40">
                    {{ __('Areas') }}
                </a>
                <a href="{{ route('pages.show', 'contact') }}" class="inline-flex items-center justify-center rounded-lg border border-luxury-border px-5 py-3 text-sm font-medium text-luxury-white transition hover:border-luxury-gold/40">
                    {{ __('Contact') }}
                </a>
            @endif
        </div>
    </div>
</body>
</html>
