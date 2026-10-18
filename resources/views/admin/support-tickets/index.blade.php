<x-admin.layouts.app :title="__('Support Tickets')">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Support Tickets') }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ __('Customer support tickets raised from the dashboard.') }}</p>
    </div>

    <form method="GET" class="mb-6 flex flex-wrap items-center gap-3">
        <div class="relative flex-1 sm:max-w-sm">
            <x-icon name="search" class="pointer-events-none absolute start-4 top-1/2 h-4 w-4 -translate-y-1/2 text-luxury-muted" />
            <input type="search" name="search" value="{{ request('search') }}" placeholder="{{ __('Search by ticket #, subject, or customer...') }}"
                class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal py-2.5 ps-11 pe-4 text-sm text-luxury-white placeholder:text-luxury-muted/70 focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
        </div>
        <select name="status" onchange="this.form.submit()"
            class="rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
            <option value="">{{ __('All Statuses') }}</option>
            @foreach (\App\Models\SupportTicket::STATUSES as $value => $label)
                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-luxury-gold px-4 py-2.5 text-sm font-semibold text-luxury-black transition hover:bg-luxury-gold-light">
            {{ __('Search') }}
        </button>
        @if (request()->filled('status') || request()->filled('search'))
            <a href="{{ route('admin.support-tickets.index') }}" class="text-xs text-luxury-muted hover:text-luxury-gold">{{ __('Clear filters') }}</a>
        @endif
    </form>

    {{-- Desktop: table --}}
    <div class="hidden overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal sm:block">
        <div class="overflow-x-auto">
            <table class="w-full text-start text-sm">
                <thead>
                    <tr class="border-b border-luxury-border text-xs uppercase tracking-wider text-luxury-muted">
                        <th class="px-6 py-3 font-medium">{{ __('Ticket') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Customer') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Subject') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Created') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Status') }}</th>
                        <th class="px-6 py-3 text-end font-medium">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-luxury-border/60">
                    @forelse ($tickets as $ticket)
                        <tr class="hover:bg-luxury-graphite">
                            <td class="px-6 py-3 font-medium text-luxury-white">{{ $ticket->ticket_number }}</td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $ticket->customer?->name ?? '—' }}</td>
                            <td class="max-w-xs px-6 py-3 text-luxury-muted">
                                <p class="truncate">{{ $ticket->subject }}</p>
                            </td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $ticket->created_at->format('M d, Y h:i A') }}</td>
                            <td class="px-6 py-3">
                                <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ match ($ticket->status) {
                                    'closed' => 'bg-luxury-slate text-luxury-muted',
                                    'in_progress' => 'bg-luxury-secondary/10 text-luxury-secondary',
                                    default => 'bg-luxury-gold/10 text-luxury-gold',
                                } }}">
                                    {{ $ticket->status_label }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-end">
                                <a href="{{ route('admin.support-tickets.show', $ticket) }}" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                    {{ __('View') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-luxury-muted">{{ __('No support tickets found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Mobile: cards --}}
    <div class="space-y-3 sm:hidden">
        @forelse ($tickets as $ticket)
            <a href="{{ route('admin.support-tickets.show', $ticket) }}"
                class="tap-scale block rounded-2xl border border-luxury-border bg-luxury-charcoal p-4 transition hover:border-luxury-gold/40">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-xs text-luxury-muted">{{ $ticket->ticket_number }} &middot; {{ $ticket->customer?->name ?? '—' }}</p>
                        <p class="mt-0.5 truncate text-sm font-medium text-luxury-white">{{ $ticket->subject }}</p>
                    </div>
                    <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-medium {{ match ($ticket->status) {
                        'closed' => 'bg-luxury-slate text-luxury-muted',
                        'in_progress' => 'bg-luxury-secondary/10 text-luxury-secondary',
                        default => 'bg-luxury-gold/10 text-luxury-gold',
                    } }}">
                        {{ $ticket->status_label }}
                    </span>
                </div>
                <p class="mt-3 border-t border-luxury-border pt-3 text-xs text-luxury-muted">{{ $ticket->created_at->format('M d, Y h:i A') }}</p>
            </a>
        @empty
            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-10 text-center text-sm text-luxury-muted">
                {{ __('No support tickets found.') }}
            </div>
        @endforelse
    </div>

    @if ($tickets->hasPages())
        <div class="mt-6 flex items-center justify-between text-sm text-luxury-muted">
            <div>
                @if ($tickets->onFirstPage())
                    <span class="cursor-not-allowed rounded-lg border border-luxury-border px-3 py-1.5 opacity-40">{{ __('Previous') }}</span>
                @else
                    <a href="{{ $tickets->previousPageUrl() }}" class="rounded-lg border border-luxury-border px-3 py-1.5 hover:border-luxury-gold/40 hover:text-luxury-gold">{{ __('Previous') }}</a>
                @endif
            </div>
            <p>{{ __('Page :current of :last', ['current' => $tickets->currentPage(), 'last' => $tickets->lastPage()]) }}</p>
            <div>
                @if ($tickets->hasMorePages())
                    <a href="{{ $tickets->nextPageUrl() }}" class="rounded-lg border border-luxury-border px-3 py-1.5 hover:border-luxury-gold/40 hover:text-luxury-gold">{{ __('Next') }}</a>
                @else
                    <span class="cursor-not-allowed rounded-lg border border-luxury-border px-3 py-1.5 opacity-40">{{ __('Next') }}</span>
                @endif
            </div>
        </div>
    @endif
</x-admin.layouts.app>
