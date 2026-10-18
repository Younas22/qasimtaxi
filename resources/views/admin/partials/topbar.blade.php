@php
    $isDashboard = request()->routeIs('admin.dashboard');
@endphp

<header class="sticky top-0 z-20 flex h-16 shrink-0 items-center gap-3 border-b border-luxury-border bg-luxury-charcoal/90 px-4 backdrop-blur sm:px-6 lg:px-8">
    {{-- Hamburger (Home) / Back (inner pages) — on mobile only, the sidebar
         is always reachable via the bottom nav's Menu tab too. --}}
    @if ($isDashboard)
        <button @click="sidebarOpen = true" class="tap-scale shrink-0 text-luxury-muted hover:text-luxury-white lg:hidden" aria-label="{{ __('Menu') }}">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
            </svg>
        </button>
    @else
        <button type="button" onclick="window.history.length > 1 ? window.history.back() : (window.location.href = '{{ route('admin.dashboard') }}')"
            class="tap-scale shrink-0 text-luxury-muted hover:text-luxury-white lg:hidden" aria-label="{{ __('Back') }}">
            <svg class="h-6 w-6 rtl:rotate-180" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
            </svg>
        </button>
    @endif

    <div class="min-w-0 flex-1">
        <h1 class="truncate text-sm font-medium text-luxury-white sm:text-luxury-muted">
            {{ $title ?? __('Overview') }}
        </h1>
    </div>

    <div class="ms-auto flex shrink-0 items-center gap-3">
        @include('admin.partials.theme-toggle')
        @include('admin.partials.currency-switcher')
        @include('admin.partials.locale-switcher')
        <x-push-notification-badge />
        @include('admin.partials.notifications')
        @include('admin.partials.profile-menu')
    </div>
</header>
