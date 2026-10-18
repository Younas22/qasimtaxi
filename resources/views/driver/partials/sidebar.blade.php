@php
    $driver = auth()->guard('driver')->user();
    $activeLanguages = \App\Models\Language::active();
    $currentLanguage = $activeLanguages->firstWhere('code', app()->getLocale()) ?? $activeLanguages->first();

    $navItems = [
        ['label' => __('Dashboard'), 'route' => 'driver.dashboard', 'icon' => 'home'],
        ['label' => __('My Rides'), 'route' => 'driver.bookings.index', 'icon' => 'car'],
        ['label' => __('Earnings'), 'route' => 'driver.earnings.index', 'icon' => 'cash'],
        ['label' => __('Reviews'), 'route' => 'driver.reviews.index', 'icon' => 'star'],
        ['label' => __('Notifications'), 'route' => 'driver.notifications.index', 'icon' => 'bell'],
        [
            'type' => 'group',
            'label' => __('Account'),
            'icon' => 'user',
            'children' => [
                ['label' => __('Profile Settings'), 'route' => 'driver.profile.edit'],
                ['label' => __('Preferences'), 'route' => 'driver.settings.edit'],
                ['label' => __('Security'), 'route' => 'driver.security.edit'],
            ],
        ],
    ];
@endphp

<div class="flex h-16 shrink-0 items-center gap-3 border-b border-luxury-border px-6">
    <a href="{{ route('driver.dashboard') }}" class="flex items-center gap-3">
        @if (setting('favicon_url'))
            <img src="{{ setting('favicon_url') }}" alt="{{ setting('company_name') }}" class="h-9 w-9 rounded-lg object-contain">
        @else
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-luxury-gold text-luxury-black font-bold">
                {{ strtoupper(substr(setting('company_name', 'Limo Schedule'), 0, 1)) }}
            </div>
        @endif
        <div class="min-w-0 leading-tight">
            <p class="truncate text-sm font-semibold tracking-wide text-luxury-white">{{ setting('company_name', config('app.name', 'Limo Schedule')) }}</p>
            <p class="text-[11px] uppercase tracking-widest text-luxury-muted">{{ __('Driver Panel') }}</p>
        </div>
    </a>
</div>

{{-- Availability — mobile menu drawer only (lg:hidden); desktop keeps it
     in the topbar. Shown first since it's the driver's #1 priority. --}}
<div class="border-b border-luxury-border p-4 lg:hidden">
    <form method="POST" action="{{ route('driver.status.toggle') }}" x-data="{ submitting: false }" @submit="submitting = true"
        class="flex items-center justify-between gap-3 rounded-xl border p-3 {{ $driver->is_online ? 'border-emerald-500/30 bg-emerald-500/5' : 'border-luxury-border' }}">
        @csrf
        <div class="flex items-center gap-2">
            <span class="h-2 w-2 rounded-full {{ $driver->is_online ? 'bg-emerald-400' : 'bg-luxury-muted' }}"></span>
            <span class="text-sm font-medium {{ $driver->is_online ? 'text-emerald-400' : 'text-luxury-white' }}">
                {{ $driver->is_online ? __('Online') : __('Offline') }}
            </span>
        </div>
        <button type="submit" :disabled="submitting" role="switch" :aria-checked="@js((bool) $driver->is_online)" aria-label="{{ __('Toggle availability') }}"
            class="tap-scale rounded-lg px-3 py-1.5 text-xs font-semibold transition disabled:opacity-60 {{ $driver->is_online ? 'border border-luxury-border text-luxury-muted' : 'bg-luxury-gold text-luxury-black' }}">
            <span x-text="submitting ? '{{ __('Updating…') }}' : '{{ $driver->is_online ? __('Go Offline') : __('Go Online') }}'"></span>
        </button>
    </form>
</div>

<nav class="scrollbar-luxury flex-1 space-y-1 overflow-y-auto px-3 py-6">
    @foreach ($navItems as $item)
        @if (($item['type'] ?? null) === 'group')
            @php
                $childIsActive = collect($item['children'])->contains(fn ($child) => request()->routeIs($child['route']));
            @endphp
            <div x-data="{ open: {{ $childIsActive ? 'true' : 'false' }} }">
                <button type="button" @click="open = !open"
                    class="tap-scale flex w-full cursor-pointer items-center gap-3 rounded-lg border-s-2 px-4 py-2.5 text-sm font-medium transition {{ $childIsActive ? 'border-luxury-gold bg-luxury-gold/10 text-luxury-gold' : 'border-transparent text-luxury-muted hover:bg-luxury-graphite hover:text-luxury-white' }}">
                    <x-icon name="{{ $item['icon'] }}" class="h-5 w-5 shrink-0" />
                    {{ $item['label'] }}
                    <x-icon name="chevron-down" class="ms-auto h-4 w-4 shrink-0 transition-transform" x-bind:class="open ? 'rotate-180' : ''" />
                </button>

                <div x-show="open" x-cloak x-transition class="ms-4 mt-1 space-y-1 border-s border-luxury-border ps-4">
                    @foreach ($item['children'] as $child)
                        <a href="{{ route($child['route']) }}"
                            class="block rounded-lg px-3 py-2 text-sm transition {{ request()->routeIs($child['route']) ? 'text-luxury-gold' : 'text-luxury-muted hover:text-luxury-white' }}">
                            {{ $child['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        @else
            <a href="{{ route($item['route']) }}"
                class="tap-scale flex items-center gap-3 rounded-lg border-s-2 px-4 py-2.5 text-sm font-medium transition {{ request()->routeIs($item['route']) ? 'border-luxury-gold bg-luxury-gold/10 text-luxury-gold' : 'border-transparent text-luxury-muted hover:bg-luxury-graphite hover:text-luxury-white' }}">
                <x-icon name="{{ $item['icon'] }}" class="h-5 w-5 shrink-0" />
                {{ $item['label'] }}
            </a>
        @endif
    @endforeach
</nav>

{{--
    Theme / language — shown here (mobile menu drawer) only. On desktop
    (lg+) the sidebar is static and these same controls live in the topbar
    instead. Dropdown opens upward (bottom-full) since this sits pinned at
    the very bottom of the drawer. Shares the shell's Alpine state
    (theme/toggleTheme/setLocale) from components/driver/layouts/app.blade.php.
--}}
<div class="space-y-3 border-t border-luxury-border p-4 lg:hidden">
    <p class="px-1 text-[11px] font-semibold uppercase tracking-wider text-luxury-muted">{{ __('Preferences') }}</p>

    <button type="button" @click="toggleTheme()"
        class="tap-scale flex h-10 w-full items-center justify-center gap-2 rounded-lg border border-luxury-border text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-white">
        <svg x-show="theme === 'dark'" x-cloak class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
        </svg>
        <svg x-show="theme === 'light'" x-cloak class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
        </svg>
        <span x-text="theme === 'dark' ? '{{ __('Dark Mode') }}' : '{{ __('Light Mode') }}'"></span>
    </button>

    @if ($currentLanguage && $activeLanguages->count() > 1)
        <div x-data="{ open: false }" class="relative">
            <button type="button" @click="open = !open" @click.outside="open = false"
                class="tap-scale flex h-10 w-full items-center gap-1.5 rounded-lg border border-luxury-border px-2.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-white">
                @if ($currentLanguage->flag_country_code)
                    <span class="fi fi-{{ $currentLanguage->flag_country_code }} shrink-0 rounded-sm"></span>
                @endif
                <span class="min-w-0 flex-1 truncate text-start">{{ $currentLanguage->native_name ?: $currentLanguage->name }}</span>
                <x-icon name="chevron-down" class="h-3.5 w-3.5 shrink-0" />
            </button>
            <div x-show="open" x-cloak x-transition class="absolute start-0 bottom-full z-30 mb-2 w-full overflow-hidden rounded-xl border border-luxury-border bg-luxury-charcoal shadow-xl">
                @foreach ($activeLanguages as $language)
                    <button type="button" @click="open = false; setLocale('{{ $language->code }}')"
                        class="flex w-full items-center gap-2 px-3 py-2.5 text-start text-xs {{ $language->code === $currentLanguage->code ? 'text-luxury-gold' : 'text-luxury-muted hover:bg-luxury-graphite hover:text-luxury-white' }}">
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

<div class="space-y-3 border-t border-luxury-border p-4">
    <a href="{{ route('pages.home') }}" class="flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-medium text-luxury-muted transition hover:text-luxury-white">
        <x-icon name="chevron-left" class="h-3.5 w-3.5 rtl:rotate-180" />
        {{ __('Back to Website') }}
    </a>
    <form method="POST" action="{{ route('driver.logout') }}">
        @csrf
        <button type="submit" class="flex w-full items-center gap-2 rounded-lg border border-luxury-border px-3 py-2.5 text-xs font-medium text-red-400 transition hover:bg-red-500/10">
            {{ __('Sign Out') }}
        </button>
    </form>
</div>
