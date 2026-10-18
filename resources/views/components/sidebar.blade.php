@props(['navPages' => null, 'currentSlug' => null])

{{--
    Assumes it renders within a parent scope exposing Alpine state
    `sidebarOpen` (declared once on layouts.public).
--}}

@php
    $navPages ??= \App\Models\Page::where('is_active', true)->get()->keyBy('slug');
    $activeLanguages = \App\Models\Language::active();
    $currentLanguage = $activeLanguages->firstWhere('code', app()->getLocale()) ?? $activeLanguages->first();
    $activeCurrencies = \App\Models\Currency::active();
    $currentCurrency = active_currency();

    // Mirrors header.blade.php's account resolution — whichever guard is
    // logged in (customer, admin, or driver) drives the mobile menu's
    // account state too.
    $publicNavAccount = null;
    if (auth()->guard('customer')->check()) {
        $navUser = auth()->guard('customer')->user();
        $publicNavAccount = [
            'name' => $navUser->name, 'email' => $navUser->email, 'avatar' => $navUser->avatar_url,
            'dashboard' => route('customer.dashboard'), 'dashboard_label' => __('My Account'),
            'logout' => route('customer.logout'),
        ];
    } elseif (auth()->guard('admin')->check()) {
        $navUser = auth()->guard('admin')->user();
        $publicNavAccount = [
            'name' => $navUser->name, 'email' => $navUser->email, 'avatar' => $navUser->avatar_url,
            'dashboard' => route('admin.dashboard'), 'dashboard_label' => __('Admin Panel'),
            'logout' => route('admin.logout'),
        ];
    } elseif (auth()->guard('driver')->check()) {
        $navUser = auth()->guard('driver')->user();
        $publicNavAccount = [
            'name' => $navUser->name, 'email' => $navUser->email, 'avatar' => $navUser->photo_url,
            'dashboard' => route('driver.dashboard'), 'dashboard_label' => __('Driver Panel'),
            'logout' => route('driver.logout'),
        ];
    }
@endphp

{{-- Overlay --}}
<div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" x-transition.opacity
    class="fixed inset-0 z-40 bg-black/70 lg:hidden"></div>

{{-- Drawer --}}
<aside x-show="sidebarOpen" x-cloak
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="-translate-x-full rtl:translate-x-full"
    x-transition:enter-end="translate-x-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="translate-x-0"
    x-transition:leave-end="-translate-x-full rtl:translate-x-full"
    class="fixed inset-y-0 start-0 z-50 flex w-[85%] max-w-sm flex-col overflow-y-auto border-e border-luxury-border bg-luxury-charcoal lg:hidden"
    @keydown.escape.window="sidebarOpen = false">

    <div class="flex h-16 shrink-0 items-center justify-between border-b border-luxury-border px-5">
        <a href="{{ route('pages.home') }}" @click="sidebarOpen = false" class="flex items-center">
            @if (setting('logo_url'))
                <img src="{{ setting('logo_url') }}" alt="{{ setting('company_name') }}" class="h-7 w-auto max-w-[190px] object-contain">
            @else
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-luxury-gold text-sm font-bold text-luxury-black">
                    {{ strtoupper(substr(setting('company_name', 'Limo Schedule'), 0, 1)) }}
                </span>
            @endif
        </a>
        <button type="button" @click="sidebarOpen = false" aria-label="{{ __('Close menu') }}"
            class="flex h-9 w-9 items-center justify-center rounded-lg text-luxury-muted hover:bg-luxury-graphite hover:text-luxury-white">
            <x-icon name="close" class="h-5 w-5" />
        </button>
    </div>

    <nav class="flex flex-1 flex-col gap-1 px-3 py-4">
        <a href="{{ route('pages.home') }}" @click="sidebarOpen = false"
            class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ $currentSlug === 'home' ? 'bg-luxury-gold/10 text-luxury-gold' : 'text-luxury-muted hover:bg-luxury-graphite hover:text-luxury-white' }}">
            <x-icon name="home" class="h-5 w-5" />
            {{ __('Home') }}
        </a>
        @foreach (\App\Models\Page::PAGES as $slug => $label)
            @continue(! isset($navPages[$slug]))
            @continue($slug === 'home' || \App\Models\Page::isExcludedFromNav($slug))
            <a href="{{ route('pages.show', $slug) }}" @click="sidebarOpen = false"
                class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ $currentSlug === $slug ? 'bg-luxury-gold/10 text-luxury-gold' : 'text-luxury-muted hover:bg-luxury-graphite hover:text-luxury-white' }}">
                <x-icon name="{{ match ($slug) {
                    'about' => 'users',
                    'services' => 'car',
                    'faq' => 'chat',
                    'contact' => 'phone',
                    default => 'star',
                } }}" class="h-5 w-5" />
                {{ __($label) }}
            </a>
        @endforeach
        <a href="{{ route('blog.index') }}" @click="sidebarOpen = false"
            class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('blog.*') ? 'bg-luxury-gold/10 text-luxury-gold' : 'text-luxury-muted hover:bg-luxury-graphite hover:text-luxury-white' }}">
            <x-icon name="pencil" class="h-5 w-5" />
            {{ __('Blog') }}
        </a>
    </nav>

    <div class="space-y-4 border-t border-luxury-border px-4 pt-4" style="padding-bottom: calc(1rem + env(safe-area-inset-bottom))">
        {{-- Theme toggle — this visitor's own preference, independent of
             Admin Settings → Light Mode (admin/customer/driver panels only). --}}
        <button type="button" @click="toggleTheme()"
            class="flex w-full cursor-pointer items-center justify-between gap-2 rounded-lg border border-luxury-border px-4 py-3 text-sm text-luxury-muted active:bg-luxury-graphite">
            <span class="flex items-center gap-2.5">
                <svg x-show="theme === 'dark'" x-cloak class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                </svg>
                <svg x-show="theme === 'light'" x-cloak class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                </svg>
                <span x-text="theme === 'dark' ? '{{ __('Dark Mode') }}' : '{{ __('Light Mode') }}'"></span>
            </span>
        </button>

        {{-- Switchers --}}
        <div class="grid grid-cols-2 gap-2">
            @if ($currentCurrency && $activeCurrencies->count() > 1)
                <div x-data="{ open: false }" class="relative">
                    <button type="button" @click="open = !open" @click.outside="open = false"
                        class="flex w-full cursor-pointer items-center justify-center gap-1.5 rounded-lg border border-luxury-border px-3 py-3 text-sm text-luxury-muted active:bg-luxury-graphite">
                        <span class="fi fi-{{ $currentCurrency->flag_country_code }} rounded-sm"></span>
                        <span class="font-semibold">{{ $currentCurrency->symbol }}</span> {{ $currentCurrency->code }}
                    </button>
                    <div x-show="open" x-cloak x-transition class="absolute start-0 bottom-full z-40 mb-2 max-h-64 w-56 overflow-y-auto rounded-xl border border-luxury-border bg-luxury-graphite shadow-xl">
                        @foreach ($activeCurrencies as $currencyOption)
                            <form method="POST" action="{{ route('currency.switch', $currencyOption->code) }}">
                                @csrf
                                <button type="submit" class="flex w-full cursor-pointer items-center gap-2.5 px-4 py-3 text-start text-sm {{ $currencyOption->code === $currentCurrency->code ? 'text-luxury-gold' : 'text-luxury-muted' }}">
                                    <span class="fi fi-{{ $currencyOption->flag_country_code }} shrink-0 rounded-sm"></span>
                                    <span class="w-5 shrink-0 text-center font-semibold">{{ $currencyOption->symbol }}</span> {{ $currencyOption->code }}
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($currentLanguage && $activeLanguages->count() > 1)
                <div x-data="{ open: false }" class="relative">
                    <button type="button" @click="open = !open" @click.outside="open = false"
                        class="flex w-full cursor-pointer items-center justify-center gap-1.5 rounded-lg border border-luxury-border px-3 py-3 text-sm text-luxury-muted active:bg-luxury-graphite">
                        @if ($currentLanguage->flag_url)
                            <img src="{{ $currentLanguage->flag_url }}" alt="" class="h-3.5 w-3.5 shrink-0 rounded-sm object-cover">
                        @elseif ($currentLanguage->flag_country_code)
                            <span class="fi fi-{{ $currentLanguage->flag_country_code }}"></span>
                        @endif
                        {{ $currentLanguage->code }}
                        <x-icon name="chevron-down" class="h-3.5 w-3.5" />
                    </button>
                    <div x-show="open" x-cloak x-transition class="absolute start-0 bottom-full z-40 mb-2 max-h-64 w-56 overflow-y-auto rounded-xl border border-luxury-border bg-luxury-graphite shadow-xl">
                        @foreach ($activeLanguages as $language)
                            <form method="POST" action="{{ route('locale.switch', $language->code) }}">
                                @csrf
                                <button type="submit" class="flex w-full cursor-pointer items-center gap-2.5 px-4 py-3 text-start text-sm {{ $language->code === $currentLanguage->code ? 'text-luxury-gold' : 'text-luxury-muted' }}">
                                    @if ($language->flag_url)
                                        <img src="{{ $language->flag_url }}" alt="" class="h-3.5 w-3.5 shrink-0 rounded-sm object-cover">
                                    @elseif ($language->flag_country_code)
                                        <span class="fi fi-{{ $language->flag_country_code }}"></span>
                                    @endif
                                    {{ $language->native_name }}
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Auth --}}
        @if ($publicNavAccount)
            <div class="space-y-2 border-t border-luxury-border pt-4">
                <div class="flex items-center gap-2 px-1">
                    <img src="{{ $publicNavAccount['avatar'] }}" alt="{{ $publicNavAccount['name'] }}"
                        class="h-8 w-8 rounded-md object-cover" onerror="this.src='https://ui-avatars.com/api/?background=c9a24b&color=0a0a0a&name={{ urlencode($publicNavAccount['name']) }}'">
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-luxury-white">{{ $publicNavAccount['name'] }}</p>
                        <p class="truncate text-xs text-luxury-muted">{{ $publicNavAccount['email'] }}</p>
                    </div>
                </div>
                <a href="{{ $publicNavAccount['dashboard'] }}" class="block rounded-lg border border-luxury-border px-4 py-2.5 text-center text-sm font-medium text-luxury-white">
                    {{ $publicNavAccount['dashboard_label'] }}
                </a>
                <form method="POST" action="{{ $publicNavAccount['logout'] }}">
                    @csrf
                    <button type="submit" class="w-full rounded-lg px-4 py-2.5 text-center text-sm font-medium text-red-400">
                        {{ __('Sign Out') }}
                    </button>
                </form>
            </div>
        @else
            <div class="flex items-center gap-2">
                @if (\Illuminate\Support\Facades\Route::has('login'))
                    <a href="{{ route('login') }}" class="flex-1 rounded-lg border border-luxury-border px-4 py-2.5 text-center text-sm font-medium text-luxury-white">
                        {{ __('Login') }}
                    </a>
                @else
                    <span class="flex-1 cursor-not-allowed rounded-lg border border-luxury-border px-4 py-2.5 text-center text-sm font-medium text-luxury-muted/40">
                        {{ __('Login') }}
                    </span>
                @endif

                @if (\Illuminate\Support\Facades\Route::has('customer.register'))
                    <a href="{{ route('customer.register') }}" class="flex-1 rounded-lg bg-luxury-gold px-4 py-2.5 text-center text-sm font-semibold text-luxury-black">
                        {{ __('Register') }}
                    </a>
                @else
                    <span class="flex-1 cursor-not-allowed rounded-lg bg-luxury-gold/40 px-4 py-2.5 text-center text-sm font-semibold text-luxury-black/60">
                        {{ __('Register') }}
                    </span>
                @endif
            </div>
        @endif
    </div>
</aside>
