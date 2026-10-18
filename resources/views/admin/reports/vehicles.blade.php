<x-admin.layouts.app :title="__('Vehicles Report')">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Vehicles Report') }}</h2>
            <p class="mt-1 text-sm text-luxury-muted">{{ __('Bookings and revenue generated per vehicle.') }}</p>
        </div>
        <a href="{{ route('admin.reports.index') }}" class="text-xs text-luxury-muted hover:text-luxury-gold">&larr; {{ __('Back to Reports') }}</a>
    </div>

    <div class="mb-6 flex flex-col gap-4 rounded-2xl border border-luxury-border bg-luxury-charcoal p-5 sm:flex-row sm:items-end sm:justify-between">
        <form method="GET">
            <x-admin.reports.date-filter :from="$from" :to="$to" />
        </form>
        <x-admin.reports.export-buttons report="vehicles" />
    </div>

    {{-- Desktop: table --}}
    <div class="hidden overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal sm:block">
        <div class="overflow-x-auto">
            <table class="w-full text-start text-sm">
                <thead>
                    <tr class="border-b border-luxury-border text-xs uppercase tracking-wider text-luxury-muted">
                        <th class="px-6 py-3 font-medium">{{ __('Vehicle') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Category') }}</th>
                        <th class="px-6 py-3 text-end font-medium">{{ __('Bookings') }}</th>
                        <th class="px-6 py-3 text-end font-medium">{{ __('Revenue') }}</th>
                        <th class="px-6 py-3 text-end font-medium">{{ __('Avg Rating') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-luxury-border/60">
                    @forelse ($rows as $vehicle)
                        <tr class="hover:bg-luxury-graphite">
                            <td class="px-6 py-3 font-medium text-luxury-white">{{ $vehicle->name }}</td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $vehicle->category?->name ?? '—' }}</td>
                            <td class="px-6 py-3 text-end text-luxury-muted">{{ number_format($vehicle->bookings_count) }}</td>
                            <td class="px-6 py-3 text-end font-medium text-luxury-gold">{{ currency($vehicle->revenue ?? 0) }}</td>
                            <td class="px-6 py-3 text-end text-luxury-muted">{{ $vehicle->average_rating ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-luxury-muted">{{ __('No vehicles found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Mobile: cards --}}
    <div class="space-y-3 sm:hidden">
        @forelse ($rows as $vehicle)
            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium text-luxury-white">{{ $vehicle->name }}</p>
                        <p class="truncate text-xs text-luxury-muted">{{ $vehicle->category?->name ?? '—' }}</p>
                    </div>
                    <span class="shrink-0 text-sm font-semibold text-luxury-gold">{{ currency($vehicle->revenue ?? 0) }}</span>
                </div>
                <div class="mt-3 flex items-center justify-between gap-3 border-t border-luxury-border pt-3 text-xs text-luxury-muted">
                    <span>{{ __(':count bookings', ['count' => number_format($vehicle->bookings_count)]) }}</span>
                    <span>{{ __('Rating') }}: {{ $vehicle->average_rating ?? '—' }}</span>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-10 text-center text-sm text-luxury-muted">
                {{ __('No vehicles found.') }}
            </div>
        @endforelse
    </div>

    @if ($rows->hasPages())
        <div class="mt-6 flex items-center justify-between text-sm text-luxury-muted">
            <div>
                @if ($rows->onFirstPage())
                    <span class="cursor-not-allowed rounded-lg border border-luxury-border px-3 py-1.5 opacity-40">{{ __('Previous') }}</span>
                @else
                    <a href="{{ $rows->previousPageUrl() }}" class="rounded-lg border border-luxury-border px-3 py-1.5 hover:border-luxury-gold/40 hover:text-luxury-gold">{{ __('Previous') }}</a>
                @endif
            </div>
            <p>{{ __('Page :current of :last', ['current' => $rows->currentPage(), 'last' => $rows->lastPage()]) }}</p>
            <div>
                @if ($rows->hasMorePages())
                    <a href="{{ $rows->nextPageUrl() }}" class="rounded-lg border border-luxury-border px-3 py-1.5 hover:border-luxury-gold/40 hover:text-luxury-gold">{{ __('Next') }}</a>
                @else
                    <span class="cursor-not-allowed rounded-lg border border-luxury-border px-3 py-1.5 opacity-40">{{ __('Next') }}</span>
                @endif
            </div>
        </div>
    @endif
</x-admin.layouts.app>
