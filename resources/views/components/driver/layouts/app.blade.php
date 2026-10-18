@php
    $direction = \App\Models\Language::findActiveByCode(app()->getLocale())?->direction ?? 'ltr';
    $themeMode = session('driver_theme_mode', setting('theme_mode', 'dark'));
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $direction }}" data-theme="{{ $themeMode }}" class="{{ $themeMode === 'light' ? '' : 'dark' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Driver Panel' }} — {{ setting('company_name', config('app.name', 'Limo Schedule')) }}</title>
    <link rel="icon" href="{{ setting('favicon_url') ?: asset('favicon.ico') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Windows doesn't render flag emoji, so the topbar language switcher
         uses this CSS sprite library instead (matches the public site). --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icons@7.2.3/css/flag-icons.min.css">

    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/intersect@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="min-h-screen bg-luxury-charcoal font-sans text-luxury-white antialiased">
    @include('components.page-progress')
    @include('components.notifications')

    <div x-data="driverAppShell(@js($themeMode))" class="flex min-h-screen">

        {{-- Mobile overlay --}}
        <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
             class="fixed inset-0 z-30 bg-black/70 lg:hidden" x-transition.opacity></div>

        {{-- Sidebar --}}
        <aside
            :class="sidebarOpen ? 'translate-x-0' : '{{ $direction === 'rtl' ? 'translate-x-full' : '-translate-x-full' }} lg:translate-x-0'"
            class="fixed inset-y-0 start-0 z-40 flex w-72 flex-col border-e border-luxury-border bg-luxury-charcoal transition-transform duration-300 ease-in-out lg:static lg:translate-x-0">
            @include('driver.partials.sidebar')
        </aside>

        {{-- Main column --}}
        <div class="flex min-h-screen flex-1 flex-col">
            @include('driver.partials.topbar')

            <main class="flex-1 px-4 py-6 pb-24 sm:px-6 lg:px-8 lg:pb-6">
                @if (session('status'))
                    <div class="mb-6 rounded-lg border border-luxury-gold/30 bg-luxury-gold/10 px-4 py-3 text-sm text-luxury-gold" role="status">
                        {{ session('status') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-400" role="alert">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="dashboard-page-enter">
                    {{ $slot }}
                </div>
            </main>
        </div>

        {{-- Sticky bottom navigation (mobile/tablet) --}}
        @include('driver.partials.bottom-nav')
    </div>

    <script>
        // Shared shell state for the whole driver panel: sidebar open/closed,
        // plus dark/light theme and language — lifted up here (rather than
        // scoped to just the topbar) so both the topbar and the mobile menu
        // drawer can read/change the same theme/language state.
        function driverAppShell(initialTheme) {
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
                    return fetch('{{ route('driver.settings.update') }}', {
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

    @stack('scripts')

    @if (auth()->guard('driver')->user()?->is_online)
        <script>
            (function () {
                if (! navigator.geolocation) return;

                const endpoint = '{{ route('driver.location.update') }}';
                const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                const MIN_INTERVAL_MS = 8000;
                // Heartbeat even when the driver is standing still —
                // watchPosition only fires on movement, and pricing needs
                // the position to stay fresh.
                const HEARTBEAT_MS = 30000;
                let lastSentAt = 0;

                function report(position, force = false) {
                    const now = Date.now();
                    if (! force && now - lastSentAt < MIN_INTERVAL_MS) return;
                    lastSentAt = now;

                    fetch(endpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({
                            lat: position.coords.latitude,
                            lng: position.coords.longitude,
                        }),
                        keepalive: true,
                    }).catch(() => {});
                }

                navigator.geolocation.watchPosition(report, () => {}, {
                    enableHighAccuracy: true,
                    maximumAge: 5000,
                    timeout: 15000,
                });

                setInterval(() => {
                    navigator.geolocation.getCurrentPosition((position) => report(position, true), () => {}, {
                        enableHighAccuracy: true,
                        maximumAge: 10000,
                        timeout: 15000,
                    });
                }, HEARTBEAT_MS);
            })();
        </script>
    @endif

    <x-push-sound-listener />
</body>
</html>
