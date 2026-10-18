@props(['notification'])

@php
    $icons = [
        'booking_created' => ['path' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'color' => 'text-luxury-secondary bg-luxury-secondary/10'],
        'booking_confirmed' => ['path' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'text-emerald-400 bg-emerald-500/10'],
        'payment_successful' => ['path' => 'M2.25 8.25h19.5M2.25 8.25v10.5a1.5 1.5 0 001.5 1.5h16.5a1.5 1.5 0 001.5-1.5V8.25M2.25 8.25l1.72-3.44a1.5 1.5 0 011.342-.81h13.376a1.5 1.5 0 011.342.81l1.72 3.44M12 15.75a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z', 'color' => 'text-luxury-gold bg-luxury-gold/10'],
        'booking_cancelled' => ['path' => 'M6 18L18 6M6 6l12 12', 'color' => 'text-red-400 bg-red-500/10'],
    ];
    $icon = $icons[$notification->data['event_type'] ?? ''] ?? ['path' => 'M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0', 'color' => 'text-luxury-muted bg-luxury-slate'];
    $isUnread = is_null($notification->read_at);
@endphp

<form method="POST" action="{{ route('admin.notifications.read', $notification->id) }}" class="block">
    @csrf
    <button type="submit" class="flex w-full items-start gap-3 px-4 py-3 text-start transition hover:bg-luxury-graphite {{ $isUnread ? 'bg-luxury-gold/[0.03]' : '' }}">
        <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $icon['color'] }}">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon['path'] }}" />
            </svg>
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
