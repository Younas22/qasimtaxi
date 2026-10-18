<x-customer.layouts.app :title="__('Notifications')">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Notifications') }}</h2>
            <p class="mt-1 text-sm text-luxury-muted">{{ __('Stay up to date on your bookings and account.') }}</p>
        </div>
        @if ($notifications->whereNull('read_at')->isNotEmpty())
            <form method="POST" action="{{ route('customer.notifications.read-all') }}">
                @csrf
                <button type="submit" class="text-sm font-medium text-luxury-gold hover:text-luxury-gold-light">{{ __('Mark all read') }}</button>
            </form>
        @endif
    </div>

    <x-push-notification-toggle class="mb-6" />

    <div class="overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal">
        <div class="divide-y divide-luxury-border/60">
            @forelse ($notifications as $notification)
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
                <div class="flex items-start gap-4 px-6 py-4 {{ $notification->read_at ? '' : 'bg-luxury-gold/5' }}">
                    <span class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-full {{ $icon[2] }} {{ $icon[1] }}">
                        <x-icon :name="$icon[0]" class="h-5 w-5" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm text-luxury-white">{{ $notification->data['title'] ?? $notification->data['message'] ?? __('Notification') }}</p>
                        @if (! empty($notification->data['message']) && ! empty($notification->data['title']))
                            <p class="mt-0.5 text-sm text-luxury-muted">{{ $notification->data['message'] }}</p>
                        @endif
                        <p class="mt-1 text-xs text-luxury-muted">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                    <div class="flex shrink-0 items-center gap-3">
                        @unless ($notification->read_at)
                            <form method="POST" action="{{ route('customer.notifications.read', $notification->id) }}">
                                @csrf
                                <button type="submit" class="text-xs font-medium text-luxury-gold hover:text-luxury-gold-light">{{ __('Mark read') }}</button>
                            </form>
                        @endunless
                        <form method="POST" action="{{ route('customer.notifications.destroy', $notification->id) }}" onsubmit="return confirm('{{ __('Delete this notification?') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-luxury-muted transition hover:text-red-400" aria-label="{{ __('Delete notification') }}">
                                <x-icon name="close" class="h-4 w-4" />
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="px-6 py-16 text-center">
                    <x-icon name="bell" class="mx-auto h-10 w-10 text-luxury-muted" />
                    <p class="mt-4 text-sm text-luxury-muted">{{ __("You're all caught up — no notifications yet.") }}</p>
                </div>
            @endforelse
        </div>
    </div>

    @if ($notifications->hasPages())
        <div class="mt-6 flex items-center justify-between text-sm text-luxury-muted">
            <div>
                @if ($notifications->onFirstPage())
                    <span class="cursor-not-allowed rounded-lg border border-luxury-border px-3 py-1.5 opacity-40">{{ __('Previous') }}</span>
                @else
                    <a href="{{ $notifications->previousPageUrl() }}" class="rounded-lg border border-luxury-border px-3 py-1.5 hover:border-luxury-gold/40 hover:text-luxury-gold">{{ __('Previous') }}</a>
                @endif
            </div>
            <p>{{ __('Page :current of :last', ['current' => $notifications->currentPage(), 'last' => $notifications->lastPage()]) }}</p>
            <div>
                @if ($notifications->hasMorePages())
                    <a href="{{ $notifications->nextPageUrl() }}" class="rounded-lg border border-luxury-border px-3 py-1.5 hover:border-luxury-gold/40 hover:text-luxury-gold">{{ __('Next') }}</a>
                @else
                    <span class="cursor-not-allowed rounded-lg border border-luxury-border px-3 py-1.5 opacity-40">{{ __('Next') }}</span>
                @endif
            </div>
        </div>
    @endif
</x-customer.layouts.app>
