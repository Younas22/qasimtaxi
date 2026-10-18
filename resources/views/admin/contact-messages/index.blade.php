<x-admin.layouts.app :title="__('Contact Messages')">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Contact Messages') }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ __('Submissions from the public Contact page form.') }}</p>
    </div>

    {{-- Filter --}}
    <form method="GET" class="mb-6 flex flex-wrap items-center gap-3">
        <select name="status" onchange="this.form.submit()"
            class="rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
            <option value="">{{ __('All Messages') }}</option>
            <option value="unread" @selected(request('status') === 'unread')>{{ __('Unread') }}</option>
            <option value="read" @selected(request('status') === 'read')>{{ __('Read') }}</option>
        </select>

        @if (request()->filled('status'))
            <a href="{{ route('admin.contact-messages.index') }}" class="text-xs text-luxury-muted hover:text-luxury-gold">{{ __('Clear filter') }}</a>
        @endif
    </form>

    {{-- Desktop: table --}}
    <div class="hidden overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal sm:block">
        <div class="overflow-x-auto">
            <table class="w-full text-start text-sm">
                <thead>
                    <tr class="border-b border-luxury-border text-xs uppercase tracking-wider text-luxury-muted">
                        <th class="px-6 py-3 font-medium">{{ __('From') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Subject') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Received') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Status') }}</th>
                        <th class="px-6 py-3 text-end font-medium">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-luxury-border/60">
                    @forelse ($messages as $message)
                        <tr class="hover:bg-luxury-graphite">
                            <td class="px-6 py-3">
                                <p class="font-medium text-luxury-white">{{ $message->name }}</p>
                                <p class="text-xs text-luxury-muted">{{ $message->email }}</p>
                            </td>
                            <td class="max-w-xs px-6 py-3 text-luxury-muted">
                                <p class="truncate">{{ $message->subject ?: '—' }}</p>
                            </td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $message->created_at->format('M d, Y h:i A') }}</td>
                            <td class="px-6 py-3">
                                @if ($message->is_read)
                                    <span class="rounded-full bg-luxury-graphite px-2.5 py-1 text-xs font-medium text-luxury-muted">{{ __('Read') }}</span>
                                @else
                                    <span class="rounded-full bg-luxury-gold/10 px-2.5 py-1 text-xs font-medium text-luxury-gold">{{ __('Unread') }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    <a href="{{ route('admin.contact-messages.show', $message) }}" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                        {{ __('View') }}
                                    </a>
                                    @permission('messages.delete')
                                        <form method="POST" action="{{ route('admin.contact-messages.destroy', $message) }}" onsubmit="return confirm(@js(__('Delete this message?')));">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg border border-red-500/30 px-3 py-1.5 text-xs font-medium text-red-400 transition hover:bg-red-500/10">
                                                {{ __('Delete') }}
                                            </button>
                                        </form>
                                    @endpermission
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-luxury-muted">{{ __('No messages received yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Mobile: cards --}}
    <div class="space-y-3 sm:hidden">
        @forelse ($messages as $message)
            <a href="{{ route('admin.contact-messages.show', $message) }}"
                class="tap-scale block rounded-2xl border border-luxury-border bg-luxury-charcoal p-4 transition hover:border-luxury-gold/40">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium text-luxury-white">{{ $message->name }}</p>
                        <p class="truncate text-xs text-luxury-muted">{{ $message->email }}</p>
                    </div>
                    @if ($message->is_read)
                        <span class="shrink-0 rounded-full bg-luxury-graphite px-2.5 py-1 text-xs font-medium text-luxury-muted">{{ __('Read') }}</span>
                    @else
                        <span class="shrink-0 rounded-full bg-luxury-gold/10 px-2.5 py-1 text-xs font-medium text-luxury-gold">{{ __('Unread') }}</span>
                    @endif
                </div>
                <p class="mt-3 truncate border-t border-luxury-border pt-3 text-xs text-luxury-white">{{ $message->subject ?: __('(No subject)') }}</p>
                <p class="mt-1 text-[11px] text-luxury-muted">{{ $message->created_at->format('M d, Y h:i A') }}</p>
            </a>
        @empty
            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-10 text-center text-sm text-luxury-muted">
                {{ __('No messages received yet.') }}
            </div>
        @endforelse
    </div>

    @if ($messages->hasPages())
        <div class="mt-6 flex items-center justify-between text-sm text-luxury-muted">
            <div>
                @if ($messages->onFirstPage())
                    <span class="cursor-not-allowed rounded-lg border border-luxury-border px-3 py-1.5 opacity-40">{{ __('Previous') }}</span>
                @else
                    <a href="{{ $messages->previousPageUrl() }}" class="rounded-lg border border-luxury-border px-3 py-1.5 hover:border-luxury-gold/40 hover:text-luxury-gold">{{ __('Previous') }}</a>
                @endif
            </div>
            <p>{{ __('Page :current of :last', ['current' => $messages->currentPage(), 'last' => $messages->lastPage()]) }}</p>
            <div>
                @if ($messages->hasMorePages())
                    <a href="{{ $messages->nextPageUrl() }}" class="rounded-lg border border-luxury-border px-3 py-1.5 hover:border-luxury-gold/40 hover:text-luxury-gold">{{ __('Next') }}</a>
                @else
                    <span class="cursor-not-allowed rounded-lg border border-luxury-border px-3 py-1.5 opacity-40">{{ __('Next') }}</span>
                @endif
            </div>
        </div>
    @endif
</x-admin.layouts.app>
