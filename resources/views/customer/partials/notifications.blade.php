@php
    $customer = auth()->guard('customer')->user();
    $recentNotifications = $customer->notifications()->latest()->limit(8)->get();
    $unreadCount = $customer->unreadNotifications()->count();
@endphp

<div x-data="{ open: false }" class="relative">
    <button @click="open = !open" @click.outside="open = false"
        class="tap-scale relative flex h-10 w-10 items-center justify-center rounded-lg border border-luxury-border text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
        <x-icon name="bell" class="h-5 w-5" />
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
                <form method="POST" action="{{ route('customer.notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="text-xs text-luxury-gold hover:text-luxury-gold-light">{{ __('Mark all read') }}</button>
                </form>
            @endif
        </div>

        <div class="scrollbar-luxury max-h-96 divide-y divide-luxury-border/60 overflow-y-auto">
            @forelse ($recentNotifications as $notification)
                @php
                    $icon = match ($notification->data['event_type'] ?? null) {
                        'booking_confirmed' => ['check-circle', 'text-emerald-400', 'bg-emerald-500/10'],
                        'booking_cancelled' => ['close', 'text-red-400', 'bg-red-500/10'],
                        'driver_assigned' => ['car', 'text-luxury-gold', 'bg-luxury-gold/10'],
                        'payment_successful' => ['cash', 'text-emerald-400', 'bg-emerald-500/10'],
                        'support_ticket_replied' => ['chat', 'text-luxury-gold', 'bg-luxury-gold/10'],
                        default => ['bell', 'text-luxury-gold', 'bg-luxury-gold/10'],
                    };
                @endphp
                <div class="flex items-start gap-3 px-4 py-3 {{ $notification->read_at ? '' : 'bg-luxury-gold/5' }}">
                    <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $icon[2] }} {{ $icon[1] }}">
                        <x-icon :name="$icon[0]" class="h-4 w-4" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm text-luxury-white">{{ $notification->data['title'] ?? $notification->data['message'] ?? __('Notification') }}</p>
                        <p class="mt-0.5 text-xs text-luxury-muted">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                </div>
            @empty
                <p class="px-4 py-6 text-center text-sm text-luxury-muted">{{ __('No notifications') }}</p>
            @endforelse
        </div>

        <a href="{{ route('customer.notifications.index') }}" class="block border-t border-luxury-border px-4 py-3 text-center text-xs font-medium text-luxury-gold hover:text-luxury-gold-light">
            {{ __('View All') }}
        </a>
    </div>
</div>
