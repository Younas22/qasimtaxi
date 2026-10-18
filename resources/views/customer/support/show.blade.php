<x-customer.layouts.app :title="$ticket->ticket_number">
    <div class="mb-6">
        <a href="{{ route('customer.support.index') }}" class="mb-2 inline-flex items-center gap-1.5 text-sm text-luxury-muted hover:text-luxury-white">
            <x-icon name="chevron-left" class="h-4 w-4 rtl:rotate-180" />
            {{ __('Back to Support') }}
        </a>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-semibold text-luxury-white">{{ $ticket->subject }}</h2>
                <p class="mt-1 text-sm text-luxury-muted">
                    {{ $ticket->ticket_number }} &middot; {{ __('Opened :date', ['date' => $ticket->created_at->format('M d, Y')]) }}
                    @if ($ticket->booking)
                        &middot; {{ __('Booking') }} {{ $ticket->booking->booking_number }}
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-2">
                <x-customer.ticket-status-badge :status="$ticket->status" />
                @if ($ticket->status !== 'closed')
                    <x-confirm-modal
                        action="{{ route('customer.support.close', $ticket) }}"
                        title="{{ __('Close This Ticket') }}"
                        message="{{ __('You can still view this ticket after closing it, but no further replies can be added.') }}"
                        confirm-label="{{ __('Close Ticket') }}"
                        trigger-label="{{ __('Close Ticket') }}"
                        trigger-class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-red-500/40 hover:text-red-400" />
                @endif
            </div>
        </div>
    </div>

    <div class="mx-auto max-w-2xl space-y-4">
        {{-- Original message --}}
        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-5">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-luxury-white">{{ $ticket->customer?->name }}</p>
                <p class="text-xs text-luxury-muted">{{ $ticket->created_at->format('M d, Y — h:i A') }}</p>
            </div>
            <p class="mt-2 whitespace-pre-line text-sm text-luxury-muted">{{ $ticket->message }}</p>
        </div>

        {{-- Reply thread --}}
        @foreach ($ticket->replies as $reply)
            <div class="rounded-2xl border p-5 {{ $reply->isFromAdmin() ? 'border-luxury-gold/30 bg-luxury-gold/5' : 'border-luxury-border bg-luxury-charcoal' }}">
                <div class="flex items-center justify-between">
                    <p class="flex items-center gap-2 text-sm font-medium text-luxury-white">
                        {{ $reply->authorName() }}
                        @if ($reply->isFromAdmin())
                            <span class="rounded-full bg-luxury-gold/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-luxury-gold">{{ __('Support Team') }}</span>
                        @endif
                    </p>
                    <p class="text-xs text-luxury-muted">{{ $reply->created_at->format('M d, Y — h:i A') }}</p>
                </div>
                <p class="mt-2 whitespace-pre-line text-sm text-luxury-muted">{{ $reply->message }}</p>
            </div>
        @endforeach

        {{-- Reply form --}}
        @if ($ticket->status !== 'closed')
            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-5">
                <form method="POST" action="{{ route('customer.support.reply', $ticket) }}" class="space-y-3">
                    @csrf
                    <textarea name="message" rows="4" required placeholder="{{ __('Type your reply...') }}"
                        class="w-full rounded-lg border border-luxury-border bg-luxury-graphite px-4 py-3 text-sm text-luxury-white placeholder:text-luxury-muted/70 focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">{{ old('message') }}</textarea>
                    <x-admin.input-error :messages="$errors->get('message')" />
                    <x-admin.button type="submit" variant="primary">{{ __('Send Reply') }}</x-admin.button>
                </form>
            </div>
        @else
            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-5 text-center">
                <p class="text-sm text-luxury-muted">{{ __('This ticket is closed.') }}</p>
            </div>
        @endif
    </div>
</x-customer.layouts.app>
