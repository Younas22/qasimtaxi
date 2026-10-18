@props(['currentSlug' => null])

{{--
    Assumes it renders within a parent scope exposing Alpine state
    `sidebarOpen` and `searchOpen` (declared once on layouts.public).
    App-style fixed tab bar — mobile only, with a raised center "Book" CTA.
--}}

@php
    $whatsappDigits = setting('whatsapp') ? preg_replace('/\D+/', '', setting('whatsapp')) : null;
    $phone = setting('phone');
    // Base 5 (Home, Services, Book, Search, Menu) plus WhatsApp and/or Call
    // when configured. Written out in full so Tailwind's build-time class
    // scanner (which only matches literal text) picks these up.
    $columnsClass = match (true) {
        $whatsappDigits && $phone => 'grid-cols-7',
        $whatsappDigits || $phone => 'grid-cols-6',
        default => 'grid-cols-5',
    };
@endphp

<nav class="fixed inset-x-0 bottom-0 z-30 border-t border-luxury-border bg-luxury-charcoal/95 backdrop-blur lg:hidden"
    style="padding-bottom: env(safe-area-inset-bottom)">
    <div class="grid {{ $columnsClass }} items-end">
        <a href="{{ route('pages.home') }}" class="tap-scale relative flex flex-col items-center gap-1 py-2.5 text-[11px] font-medium {{ $currentSlug === 'home' ? 'text-luxury-gold' : 'text-luxury-muted' }}">
            <span class="absolute inset-x-3 top-1 -z-10 h-8 rounded-full bg-luxury-gold/10 transition-opacity {{ $currentSlug === 'home' ? 'opacity-100' : 'opacity-0' }}"></span>
            <x-icon name="home" class="h-5 w-5" />
            {{ __('Home') }}
        </a>

        <a href="{{ route('pages.show', 'services') }}" class="tap-scale relative flex flex-col items-center gap-1 py-2.5 text-[11px] font-medium {{ $currentSlug === 'services' ? 'text-luxury-gold' : 'text-luxury-muted' }}">
            <span class="absolute inset-x-3 top-1 -z-10 h-8 rounded-full bg-luxury-gold/10 transition-opacity {{ $currentSlug === 'services' ? 'opacity-100' : 'opacity-0' }}"></span>
            <x-icon name="car" class="h-5 w-5" />
            {{ __('Services') }}
        </a>

        @if ($whatsappDigits)
            <a href="https://wa.me/{{ $whatsappDigits }}?text={{ rawurlencode(__("Hi! I'd like to inquire about booking a ride.")) }}" target="_blank" rel="noopener"
                class="tap-scale relative flex flex-col items-center gap-1 py-2.5 text-[11px] font-medium text-luxury-muted">
                <x-whatsapp-icon class="h-5 w-5 text-[#25D366]" />
                {{ __('WhatsApp') }}
            </a>
        @endif

        {{-- Raised center CTA --}}
        <a href="{{ route('pages.home') }}#booking-widget" class="relative flex flex-col items-center">
            <span class="tap-scale absolute -top-6 flex h-14 w-14 items-center justify-center rounded-full bg-luxury-gold text-luxury-black shadow-lg shadow-black/40 ring-4 ring-luxury-charcoal transition hover:bg-luxury-gold-light">
                <x-icon name="calendar" class="h-6 w-6" />
            </span>
            <span class="mt-8 pb-2 text-[11px] font-semibold text-luxury-gold">{{ __('Book') }}</span>
        </a>

        @if ($phone)
            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}" class="tap-scale flex flex-col items-center gap-1 py-2.5 text-[11px] font-medium text-luxury-muted">
                <x-icon name="phone" class="h-5 w-5" />
                {{ __('Call') }}
            </a>
        @endif

        <button type="button" @click="searchOpen = true" class="tap-scale flex flex-col items-center gap-1 py-2.5 text-[11px] font-medium text-luxury-muted">
            <x-icon name="search" class="h-5 w-5" />
            {{ __('Search') }}
        </button>

        <button type="button" @click="sidebarOpen = true" class="tap-scale flex flex-col items-center gap-1 py-2.5 text-[11px] font-medium text-luxury-muted">
            <x-icon name="menu" class="h-5 w-5" />
            {{ __('Menu') }}
        </button>
    </div>
</nav>
