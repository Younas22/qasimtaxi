@php
    $admin = auth()->guard('admin')->user();
    $recentNotifications = $admin->notifications()->latest()->limit(8)->get();
    $unreadCount = $admin->unreadNotifications()->count();
@endphp

<div x-data="{ open: false }" class="relative">
    <button @click="open = !open" @click.outside="open = false"
        class="relative flex h-10 w-10 items-center justify-center rounded-lg border border-luxury-border text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
        </svg>
        @if ($unreadCount > 0)
            <span class="absolute -end-1 -top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-luxury-gold px-1 text-[10px] font-bold text-luxury-black">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    <div x-show="open" x-cloak x-transition
        class="absolute end-0 z-30 mt-2 w-80 overflow-hidden rounded-xl border border-luxury-border bg-luxury-charcoal shadow-xl sm:w-96">
        <div class="flex items-center justify-between border-b border-luxury-border px-4 py-3">
            <p class="text-sm font-semibold text-luxury-white">{{ __('Notifications') }}</p>
            @if ($unreadCount > 0)
                <form method="POST" action="{{ route('admin.notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="text-xs text-luxury-gold hover:text-luxury-gold-light">{{ __('Mark all read') }}</button>
                </form>
            @endif
        </div>

        <div class="scrollbar-luxury max-h-96 divide-y divide-luxury-border/60 overflow-y-auto">
            @forelse ($recentNotifications as $notification)
                <x-admin.notification-item :notification="$notification" />
            @empty
                <p class="px-4 py-6 text-center text-sm text-luxury-muted">{{ __('No notifications') }}</p>
            @endforelse
        </div>

        <a href="{{ route('admin.notifications.index') }}" class="block border-t border-luxury-border px-4 py-3 text-center text-xs font-medium text-luxury-gold hover:text-luxury-gold-light">
            {{ __('View All') }}
        </a>
    </div>
</div>
