<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ \App\Models\Language::findActiveByCode(app()->getLocale())?->direction ?? 'ltr' }}" data-theme="{{ setting('theme_mode', 'dark') }}" class="{{ setting('theme_mode', 'dark') === 'light' ? '' : 'dark' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 — Access Denied</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('admin.partials.theme-vars')
</head>
<body class="flex min-h-screen items-center justify-center bg-luxury-black px-4 font-sans text-luxury-white antialiased">
    <div class="pointer-events-none fixed inset-0 overflow-hidden">
        <div class="absolute left-1/2 top-0 h-96 w-96 -translate-x-1/2 rounded-full bg-luxury-gold/10 blur-3xl"></div>
    </div>

    <div class="relative w-full max-w-md text-center">
        <p class="text-6xl font-semibold text-luxury-gold">403</p>
        <h1 class="mt-4 text-xl font-semibold text-luxury-white">Access Denied</h1>
        <p class="mt-2 text-sm text-luxury-muted">
            {{ $exception->getMessage() ?: "You don't have permission to access this page." }}
        </p>

        @if (\Illuminate\Support\Facades\Route::has('admin.dashboard'))
            <a href="{{ route('admin.dashboard') }}" class="mt-6 inline-flex items-center justify-center rounded-lg bg-luxury-gold px-5 py-3 text-sm font-semibold text-luxury-black transition hover:bg-luxury-gold-light">
                Back to Dashboard
            </a>
        @endif
    </div>
</body>
</html>
