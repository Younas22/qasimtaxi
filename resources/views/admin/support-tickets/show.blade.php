<x-admin.layouts.app :title="$ticket->ticket_number">
    <div class="mb-6">
        <a href="{{ route('admin.support-tickets.index') }}" class="mb-2 inline-block text-sm text-luxury-muted hover:text-luxury-white">&larr; {{ __('Back to Support Tickets') }}</a>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-semibold text-luxury-white">{{ $ticket->subject }}</h2>
                <p class="mt-1 text-sm text-luxury-muted">
                    {{ $ticket->ticket_number }} &middot; {{ $ticket->customer?->name }} ({{ $ticket->customer?->email }})
                    @if ($ticket->booking)
                        &middot; {{ __('Booking :number', ['number' => $ticket->booking->booking_number]) }}
                    @endif
                </p>
            </div>

            @permission('support.edit')
                <form method="POST" action="{{ route('admin.support-tickets.status', $ticket) }}">
                    @csrf
                    @method('PUT')
                    <select name="status" onchange="this.form.submit()"
                        class="rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                        @foreach (\App\Models\SupportTicket::STATUSES as $value => $label)
                            <option value="{{ $value }}" @selected($ticket->status === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </form>
            @else
                <span class="rounded-full bg-luxury-slate px-3 py-1.5 text-sm font-medium text-luxury-muted">{{ $ticket->status_label }}</span>
            @endpermission
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
                            <span class="rounded-full bg-luxury-gold/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-luxury-gold">{{ __('Staff') }}</span>
                        @endif
                    </p>
                    <p class="text-xs text-luxury-muted">{{ $reply->created_at->format('M d, Y — h:i A') }}</p>
                </div>
                <p class="mt-2 whitespace-pre-line text-sm text-luxury-muted">{{ $reply->message }}</p>
            </div>
        @endforeach

        {{-- Reply form --}}
        @permission('support.edit')
            @if ($ticket->status !== 'closed')
                <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-5">
                    <form method="POST" action="{{ route('admin.support-tickets.reply', $ticket) }}" class="space-y-3">
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
        @endpermission
    </div>
</x-admin.layouts.app>
