@php
    $direction = \App\Models\Language::findActiveByCode(app()->getLocale())?->direction ?? 'ltr';
    $themeMode = session('customer_theme_mode', setting('theme_mode', 'dark'));
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $direction }}"
    data-theme="{{ $themeMode }}" class="{{ $themeMode === 'light' ? '' : 'dark' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'My Account' }} — {{ setting('company_name', config('app.name', 'Limo Schedule')) }}</title>
    <link rel="icon" href="{{ setting('favicon_url') ?: asset('favicon.ico') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="flex min-h-screen items-center justify-center bg-luxury-charcoal px-4 font-sans text-luxury-white antialiased">
    @include('components.page-progress')
    @include('components.notifications')

    <div class="pointer-events-none fixed inset-0 overflow-hidden">
        <div class="absolute left-1/2 top-0 h-96 w-96 -translate-x-1/2 rounded-full bg-luxury-gold/10 blur-3xl"></div>
    </div>

    <div class="dashboard-page-enter relative w-full max-w-md py-10">
        <div class="mb-8 flex flex-col items-center gap-3">
            <a href="{{ route('pages.home') }}" class="flex flex-col items-center gap-3">
                @if (setting('logo_url'))
                    {{-- Natural aspect ratio, not squeezed into a fixed
                         square box — the real logo is a wide wordmark, and
                         forcing it into a 48×48 square shrank it down to a
                         barely-visible sliver. The company-name caption
                         below is dropped when a real logo renders (it
                         already includes the name); the square lettermark
                         fallback below is unaffected. --}}
                    <img src="{{ setting('logo_url') }}" alt="{{ setting('company_name') }}" class="h-12 w-auto max-w-[240px] object-contain">
                @else
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-luxury-gold text-lg font-bold text-luxury-black">
                        {{ strtoupper(substr(setting('company_name', 'Limo Schedule'), 0, 1)) }}
                    </div>
                    <p class="text-sm uppercase tracking-[0.3em] text-luxury-muted">{{ setting('company_name', config('app.name', 'Limo Schedule')) }}</p>
                @endif
            </a>
        </div>

        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-8 shadow-2xl">
            {{ $slot }}
        </div>

        <p class="mt-6 text-center text-xs text-luxury-muted">&copy; {{ now()->year }} {{ setting('company_name', config('app.name')) }}. {{ __('All rights reserved.') }}</p>
    </div>
</body>
</html>
