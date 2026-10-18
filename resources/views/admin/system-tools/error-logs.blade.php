<x-admin.layouts.app :title="__('Error Logs')">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Error Logs') }}</h2>
            <p class="mt-1 text-sm text-luxury-muted">{{ __('Most recent :count entries from storage/logs/laravel.log (:size KB).', ['count' => count($entries), 'size' => $logSize]) }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.system-tools.index') }}" class="text-xs text-luxury-muted hover:text-luxury-gold">&larr; {{ __('Back to System Tools') }}</a>
            @permission('system.edit')
                <form method="POST" action="{{ route('admin.system-tools.error-logs.clear') }}" onsubmit="return confirm('{{ __('Clear the entire error log? This cannot be undone.') }}');">
                    @csrf
                    <button type="submit" class="rounded-lg border border-red-500/30 px-4 py-2 text-xs font-medium text-red-400 transition hover:bg-red-500/10">
                        {{ __('Clear Log') }}
                    </button>
                </form>
            @endpermission
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal">
        <div class="overflow-x-auto">
            <table class="w-full text-start text-sm">
                <thead>
                    <tr class="border-b border-luxury-border text-xs uppercase tracking-wider text-luxury-muted">
                        <th class="px-6 py-3 font-medium">{{ __('Level') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Timestamp') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Message') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-luxury-border/60">
                    @forelse ($entries as $entry)
                        <tr class="hover:bg-luxury-graphite">
                            <td class="px-6 py-3">
                                <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ match ($entry['level']) {
                                    'ERROR', 'CRITICAL', 'EMERGENCY', 'ALERT' => 'bg-red-500/10 text-red-400',
                                    'WARNING' => 'bg-luxury-gold/10 text-luxury-gold',
                                    default => 'bg-luxury-secondary/10 text-luxury-secondary',
                                } }}">
                                    {{ $entry['level'] }}
                                </span>
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap text-luxury-muted">{{ $entry['timestamp'] }}</td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $entry['message'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-10 text-center text-luxury-muted">{{ __('No log entries found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin.layouts.app>
