@php
    $direction = \App\Models\Language::findActiveByCode(app()->getLocale())?->direction ?? 'ltr';
    // The admin panel's own dark/light preference — session-scoped and
    // separate from Setting::theme_mode (the public website's theme), so
    // toggling it here never changes what customers see on the live site.
    $adminThemeMode = session('admin_theme_mode', setting('theme_mode', 'dark'));
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $direction }}" data-theme="{{ $adminThemeMode }}" class="{{ $adminThemeMode === 'light' ? '' : 'dark' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} — {{ setting('company_name', config('app.name', 'Limo Schedule')) }}</title>
    <link rel="icon" href="{{ setting('favicon_url') ?: asset('favicon.ico') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Windows doesn't render flag emoji, so the topbar language switcher
         uses this CSS sprite library instead (matches driver/public). --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icons@7.2.3/css/flag-icons.min.css">

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @stack('styles')
</head>
<body class="min-h-screen bg-luxury-charcoal font-sans text-luxury-white antialiased">
    <div x-data="{ sidebarOpen: false }" class="flex min-h-screen">

        {{-- Mobile overlay --}}
        <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
             class="fixed inset-0 z-30 bg-black/70 lg:hidden" x-transition.opacity></div>

        {{-- Sidebar --}}
        <aside
            :class="sidebarOpen ? 'translate-x-0' : '{{ $direction === 'rtl' ? 'translate-x-full' : '-translate-x-full' }} lg:translate-x-0'"
            class="fixed inset-y-0 start-0 z-40 flex w-64 flex-col border-e border-luxury-border bg-luxury-charcoal transition-transform duration-300 ease-in-out lg:static lg:translate-x-0">
            @include('admin.partials.sidebar')
        </aside>

        {{-- Main column. min-w-0 matters here: a flex item's default
             min-width is "auto" (its content's min-content size), not 0 —
             without this, any page that ever renders a sufficiently wide
             piece of content deep inside (e.g. one long, unbroken string
             with no natural wrap point) forces this WHOLE column past its
             own share of the flex row, overflowing the page horizontally
             instead of just scrolling locally wherever that content lives. --}}
        <div class="flex min-h-screen min-w-0 flex-1 flex-col">
            @include('admin.partials.topbar')

            <main class="flex-1 px-4 py-6 pb-24 sm:px-6 lg:px-8 lg:pb-6">
                @if (session('status'))
                    <div class="mb-6 rounded-lg border border-luxury-gold/30 bg-luxury-gold/10 px-4 py-3 text-sm text-luxury-gold">
                        {{ session('status') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-400">
                        {{ session('error') }}
                    </div>
                @endif

                {{ $slot }}
            </main>

            @include('admin.partials.footer')
        </div>

        {{-- Sticky bottom navigation (mobile/tablet) --}}
        @include('admin.partials.bottom-nav')
    </div>

    <x-push-sound-listener />

    @stack('scripts')
</body>
</html>
