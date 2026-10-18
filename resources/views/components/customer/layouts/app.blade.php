@php
    $direction = \App\Models\Language::findActiveByCode(app()->getLocale())?->direction ?? 'ltr';
    $themeMode = session('customer_theme_mode', setting('theme_mode', 'dark'));
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $direction }}" data-theme="{{ $themeMode }}" class="{{ $themeMode === 'light' ? '' : 'dark' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'My Account' }} — {{ setting('company_name', config('app.name', 'Limo Schedule')) }}</title>
    <link rel="icon" href="{{ setting('favicon_url') ?: asset('favicon.ico') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Windows doesn't render flag emoji, so the topbar language switcher
         uses this CSS sprite library instead (matches driver/admin/public). --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icons@7.2.3/css/flag-icons.min.css">

    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/intersect@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="min-h-screen bg-luxury-charcoal font-sans text-luxury-white antialiased">
    @include('components.page-progress')
    @include('components.notifications')

    <div x-data="customerAppShell(@js($themeMode))" class="flex min-h-screen">

        {{-- Mobile overlay --}}
        <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
             class="fixed inset-0 z-30 bg-black/70 lg:hidden" x-transition.opacity></div>

        {{-- Sidebar --}}
        <aside
            :class="sidebarOpen ? 'translate-x-0' : '{{ $direction === 'rtl' ? 'translate-x-full' : '-translate-x-full' }} lg:translate-x-0'"
            class="fixed inset-y-0 start-0 z-40 flex w-72 flex-col border-e border-luxury-border bg-luxury-charcoal transition-transform duration-300 ease-in-out lg:static lg:translate-x-0">
            @include('customer.partials.sidebar')
        </aside>

        {{-- Main column --}}
        <div class="flex min-h-screen flex-1 flex-col">
            @include('customer.partials.topbar')

            <main class="flex-1 px-4 py-6 pb-24 sm:px-6 lg:px-8 lg:pb-6">
                <div class="dashboard-page-enter">
                    {{ $slot }}
                </div>
            </main>
        </div>

        {{-- Sticky bottom navigation (mobile/tablet) --}}
        @include('customer.partials.bottom-nav')
    </div>

    <script>
        // Shared shell state for the whole authenticated panel: sidebar
        // open/closed, plus dark/light theme and language — lifted up here
        // (rather than scoped to just the topbar) so both the topbar and
        // the mobile menu drawer can read/change the same theme/language
        // state. Session-scoped and independent from Setting::theme_mode
        // (the public site's theme) — see components/customer/layouts/app.blade.php.
        function customerAppShell(initialTheme) {
            return {
                sidebarOpen: false,
                theme: initialTheme,

                toggleTheme() {
                    const mode = this.theme === 'dark' ? 'light' : 'dark';
                    this.theme = mode;
                    document.documentElement.classList.toggle('dark', mode !== 'light');
                    document.documentElement.setAttribute('data-theme', mode);
                    this.save({ theme_mode: mode });
                },

                setLocale(code) {
                    this.save({ locale: code }).then(() => window.location.reload());
                },

                save(payload) {
                    return fetch('{{ route('customer.settings.update') }}', {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify(payload),
                    });
                },
            };
        }
    </script>

    <x-push-sound-listener />

    @stack('scripts')
</body>
</html>
