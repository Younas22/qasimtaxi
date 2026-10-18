<x-admin.layouts.app :title="__('Notifications')">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Notifications') }}</h2>
            <p class="mt-1 text-sm text-luxury-muted">{{ __('Booking activity across your fleet, delivered to your admin panel and email.') }}</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.notification-settings.edit') }}" class="rounded-lg border border-luxury-border px-4 py-2.5 text-sm font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                {{ __('Preferences') }}
            </a>
            @if ($notifications->getCollection()->whereNull('read_at')->isNotEmpty())
                <form method="POST" action="{{ route('admin.notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="rounded-lg border border-luxury-border px-4 py-2.5 text-sm font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                        {{ __('Mark All Read') }}
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal">
        <div class="divide-y divide-luxury-border/60">
            @forelse ($notifications as $notification)
                <div class="group relative">
                    <x-admin.notification-item :notification="$notification" />
                    <form method="POST" action="{{ route('admin.notifications.destroy', $notification->id) }}" class="absolute end-3 top-3 opacity-0 transition group-hover:opacity-100">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-md p-1 text-luxury-muted hover:text-red-400" title="{{ __('Remove') }}">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </form>
                </div>
            @empty
                <p class="px-6 py-10 text-center text-luxury-muted">{{ __('No notifications yet.') }}</p>
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
</x-admin.layouts.app>
