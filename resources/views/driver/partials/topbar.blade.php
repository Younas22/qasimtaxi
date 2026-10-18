@php
    $driver = auth()->guard('driver')->user();
    $activeLanguages = \App\Models\Language::active();
    $currentLanguage = $activeLanguages->firstWhere('code', app()->getLocale()) ?? $activeLanguages->first();
    $isDashboard = request()->routeIs('driver.dashboard');
@endphp

<header class="sticky top-0 z-20 flex h-16 shrink-0 items-center gap-3 border-b border-luxury-border bg-luxury-charcoal/90 px-4 backdrop-blur sm:px-6 lg:px-8">
    {{-- Hamburger (Home) / Back (inner pages) — on mobile only, the sidebar
         is always reachable via the bottom nav's Menu tab too. --}}
    @if ($isDashboard)
        <button @click="sidebarOpen = true" class="tap-scale shrink-0 text-luxury-muted hover:text-luxury-white lg:hidden" aria-label="{{ __('Menu') }}">
            <x-icon name="menu" class="h-6 w-6" />
        </button>
    @else
        <button type="button" onclick="window.history.length > 1 ? window.history.back() : (window.location.href = '{{ route('driver.dashboard') }}')"
            class="tap-scale shrink-0 text-luxury-muted hover:text-luxury-white lg:hidden" aria-label="{{ __('Back') }}">
            <x-icon name="chevron-left" class="h-6 w-6 rtl:rotate-180" />
        </button>
    @endif

    <div class="min-w-0 flex-1">
        <h1 class="truncate text-sm font-medium text-luxury-white sm:text-luxury-muted">{{ $title ?? __('Overview') }}</h1>
    </div>

    <div class="ms-auto flex shrink-0 items-center gap-1.5 sm:gap-2">
        {{-- Theme / language — desktop topbar only; on phones and tablets
             the same controls live in the mobile menu drawer
             (driver.partials.sidebar) so the header stays uncluttered. --}}
        <div class="hidden items-center gap-2 lg:flex">
            <button type="button" @click="toggleTheme()" aria-label="{{ __('Toggle appearance') }}"
                class="tap-scale flex h-9 w-9 items-center justify-center rounded-lg border border-luxury-border text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                <svg x-show="theme === 'dark'" x-cloak class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                </svg>
                <svg x-show="theme === 'light'" x-cloak class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                </svg>
            </button>

            @if ($activeLanguages->count() > 1)
                <div x-data="{ open: false }" class="relative inline-block">
                    <button type="button" @click="open = !open" @click.outside="open = false"
                        class="tap-scale flex h-9 w-full items-center gap-1.5 whitespace-nowrap rounded-lg border border-luxury-border px-2.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-white">
                        @if ($currentLanguage?->flag_country_code)
                            <span class="fi fi-{{ $currentLanguage->flag_country_code }} shrink-0 rounded-sm"></span>
                        @endif
                        <span>{{ $currentLanguage?->native_name ?: $currentLanguage?->name }}</span>
                        <x-icon name="chevron-down" class="h-3.5 w-3.5" />
                    </button>

                    <div x-show="open" x-cloak x-transition class="absolute end-0 top-full z-30 w-full overflow-hidden rounded-xl border border-luxury-border bg-luxury-charcoal shadow-xl">
                        @foreach ($activeLanguages as $language)
                            <button type="button" @click="open = false; setLocale('{{ $language->code }}')"
                                class="flex w-full items-center gap-2 px-3.5 py-2.5 text-start text-xs {{ $language->code === $currentLanguage?->code ? 'text-luxury-gold' : 'text-luxury-muted hover:bg-luxury-graphite hover:text-luxury-white' }}">
                                @if ($language->flag_country_code)
                                    <span class="fi fi-{{ $language->flag_country_code }} shrink-0 rounded-sm"></span>
                                @endif
                                {{ $language->native_name ?: $language->name }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Availability — always visible, every size: this is a driver's
             single most important control. --}}
        <form method="POST" action="{{ route('driver.status.toggle') }}" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            <button type="submit" :disabled="submitting" role="switch" :aria-checked="@js((bool) $driver->is_online)" aria-label="{{ __('Toggle availability') }}"
                class="tap-scale inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-semibold transition disabled:opacity-60 {{ $driver->is_online ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-400' : 'border-luxury-border text-luxury-muted hover:border-luxury-gold/40' }}">
                <svg x-show="submitting" x-cloak class="h-3 w-3 animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span x-show="!submitting" class="h-1.5 w-1.5 rounded-full {{ $driver->is_online ? 'bg-emerald-400' : 'bg-luxury-muted' }}"></span>
                <span x-text="submitting ? '{{ __('Updating…') }}' : '{{ $driver->is_online ? __('Online') : __('Offline') }}'"></span>
            </button>
        </form>

        <x-push-notification-badge />

        @include('driver.partials.notifications')

        @include('driver.partials.profile-menu')
    </div>
</header>
