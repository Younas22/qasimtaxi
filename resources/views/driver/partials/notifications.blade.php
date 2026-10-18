@php
    $driver = auth()->guard('driver')->user();
    $recentNotifications = $driver->notifications()->latest()->limit(8)->get();
    $unreadCount = $driver->unreadNotifications()->count();
@endphp

<div x-data="{ open: false }" class="relative">
    <button @click="open = !open" @click.outside="open = false"
        class="tap-scale relative flex h-9 w-9 items-center justify-center rounded-lg border border-luxury-border text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
        <x-icon name="bell" class="h-4 w-4" />
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
                <form method="POST" action="{{ route('driver.notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="text-xs text-luxury-gold hover:text-luxury-gold-light">{{ __('Mark all read') }}</button>
                </form>
            @endif
        </div>

        <div class="scrollbar-luxury max-h-96 divide-y divide-luxury-border/60 overflow-y-auto">
            @forelse ($recentNotifications as $notification)
                @php
                    $icon = match ($notification->data['event_type'] ?? null) {
                        'booking_assigned' => ['car', 'text-luxury-gold', 'bg-luxury-gold/10'],
                        'booking_cancelled' => ['close', 'text-red-400', 'bg-red-500/10'],
                        default => ['bell', 'text-luxury-muted', 'bg-luxury-slate'],
                    };
                    $isUnread = is_null($notification->read_at);
                @endphp
                <form method="POST" action="{{ route('driver.notifications.read', $notification->id) }}" class="block">
                    @csrf
                    <button type="submit" class="flex w-full items-start gap-3 px-4 py-3 text-start transition hover:bg-luxury-graphite {{ $isUnread ? 'bg-luxury-gold/[0.03]' : '' }}">
                        <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $icon[2] }} {{ $icon[1] }}">
                            <x-icon :name="$icon[0]" class="h-4 w-4" />
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="flex items-center gap-2">
                                <span class="truncate text-sm font-medium {{ $isUnread ? 'text-luxury-white' : 'text-luxury-muted' }}">{{ $notification->data['title'] ?? __('Notification') }}</span>
                                @if ($isUnread)
                                    <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-luxury-gold"></span>
                                @endif
                            </span>
                            <span class="mt-0.5 block truncate text-xs text-luxury-muted">{{ $notification->data['message'] ?? '' }}</span>
                            <span class="mt-1 block text-[11px] text-luxury-muted/70">{{ $notification->created_at->diffForHumans() }}</span>
                        </span>
                    </button>
                </form>
            @empty
                <p class="px-4 py-6 text-center text-sm text-luxury-muted">{{ __('No notifications') }}</p>
            @endforelse
        </div>

        <a href="{{ route('driver.notifications.index') }}" class="block border-t border-luxury-border px-4 py-3 text-center text-xs font-medium text-luxury-gold hover:text-luxury-gold-light">
            {{ __('View All') }}
        </a>
    </div>
</div>
