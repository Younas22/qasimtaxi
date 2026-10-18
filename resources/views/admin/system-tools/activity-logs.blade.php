<x-admin.layouts.app :title="__('Activity Logs')">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Activity Logs') }}</h2>
            <p class="mt-1 text-sm text-luxury-muted">{{ __('A record of administrative actions taken in this panel.') }}</p>
        </div>
        <a href="{{ route('admin.system-tools.index') }}" class="text-xs text-luxury-muted hover:text-luxury-gold">&larr; {{ __('Back to System Tools') }}</a>
    </div>

    <form method="GET" class="mb-6 flex flex-wrap items-center gap-3">
        <select name="action" onchange="this.form.submit()"
            class="rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
            <option value="">{{ __('All Actions') }}</option>
            @foreach ($actions as $action)
                <option value="{{ $action }}" @selected(request('action') === $action)>{{ $action }}</option>
            @endforeach
        </select>
        @if (request()->filled('action'))
            <a href="{{ route('admin.system-tools.activity-logs') }}" class="text-xs text-luxury-muted hover:text-luxury-gold">{{ __('Clear filter') }}</a>
        @endif
    </form>

    <div class="overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal">
        <div class="overflow-x-auto">
            <table class="w-full text-start text-sm">
                <thead>
                    <tr class="border-b border-luxury-border text-xs uppercase tracking-wider text-luxury-muted">
                        <th class="px-6 py-3 font-medium">{{ __('Admin') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Action') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Description') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('IP Address') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('When') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-luxury-border/60">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-luxury-graphite">
                            <td class="px-6 py-3 font-medium text-luxury-white">{{ $log->admin?->name ?? __('System') }}</td>
                            <td class="px-6 py-3">
                                <span class="rounded-full bg-luxury-slate px-2.5 py-1 text-xs font-medium text-luxury-muted">{{ $log->action }}</span>
                            </td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $log->description }}</td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $log->ip_address ?? '—' }}</td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $log->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-luxury-muted">{{ __('No activity recorded yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($logs->hasPages())
        <div class="mt-6 flex items-center justify-between text-sm text-luxury-muted">
            <div>
                @if ($logs->onFirstPage())
                    <span class="cursor-not-allowed rounded-lg border border-luxury-border px-3 py-1.5 opacity-40">{{ __('Previous') }}</span>
                @else
                    <a href="{{ $logs->previousPageUrl() }}" class="rounded-lg border border-luxury-border px-3 py-1.5 hover:border-luxury-gold/40 hover:text-luxury-gold">{{ __('Previous') }}</a>
                @endif
            </div>
            <p>{{ __('Page :current of :last', ['current' => $logs->currentPage(), 'last' => $logs->lastPage()]) }}</p>
            <div>
                @if ($logs->hasMorePages())
                    <a href="{{ $logs->nextPageUrl() }}" class="rounded-lg border border-luxury-border px-3 py-1.5 hover:border-luxury-gold/40 hover:text-luxury-gold">{{ __('Next') }}</a>
                @else
                    <span class="cursor-not-allowed rounded-lg border border-luxury-border px-3 py-1.5 opacity-40">{{ __('Next') }}</span>
                @endif
            </div>
        </div>
    @endif
</x-admin.layouts.app>
