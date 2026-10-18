<x-admin.layouts.app :title="__('Bookings Report')">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Bookings Report') }}</h2>
            <p class="mt-1 text-sm text-luxury-muted">{{ __('All bookings within the selected date range.') }}</p>
        </div>
        <a href="{{ route('admin.reports.index') }}" class="text-xs text-luxury-muted hover:text-luxury-gold">&larr; {{ __('Back to Reports') }}</a>
    </div>

    <div class="mb-6 flex flex-col gap-4 rounded-2xl border border-luxury-border bg-luxury-charcoal p-5 sm:flex-row sm:items-end sm:justify-between">
        <form method="GET">
            <x-admin.reports.date-filter :from="$from" :to="$to">
                <div>
                    <label class="mb-1 block text-xs text-luxury-muted">{{ __('Status') }}</label>
                    <select name="status" class="rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                        <option value="">{{ __('All Statuses') }}</option>
                        @foreach (\App\Models\Booking::STATUSES as $value => $label)
                            <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs text-luxury-muted">{{ __('Type') }}</label>
                    <select name="type" class="rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                        <option value="">{{ __('All Types') }}</option>
                        @foreach (\App\Models\Booking::TYPES as $value => $label)
                            <option value="{{ $value }}" @selected($type === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </x-admin.reports.date-filter>
        </form>
        <x-admin.reports.export-buttons report="bookings" />
    </div>

    <div class="mb-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-5">
        <x-admin.stat-card :label="__('Total')" :value="number_format($summary['total'])">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
        </x-admin.stat-card>
        @foreach (['completed' => __('Completed'), 'cancelled' => __('Cancelled'), 'pending' => __('Pending'), 'confirmed' => __('Confirmed')] as $key => $label)
            <x-admin.stat-card :label="$label" :value="number_format($summary['by_status'][$key] ?? 0)">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75" /></svg>
            </x-admin.stat-card>
        @endforeach
    </div>

    {{-- Desktop: table --}}
    <div class="hidden overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal sm:block">
        <div class="overflow-x-auto">
            <table class="w-full text-start text-sm">
                <thead>
                    <tr class="border-b border-luxury-border text-xs uppercase tracking-wider text-luxury-muted">
                        <th class="px-6 py-3 font-medium">{{ __('Booking') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Customer') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Driver / Vehicle') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Type') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Status') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Pickup') }}</th>
                        <th class="px-6 py-3 text-end font-medium">{{ __('Fare') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-luxury-border/60">
                    @forelse ($rows as $booking)
                        <tr class="hover:bg-luxury-graphite">
                            <td class="px-6 py-3 font-medium text-luxury-white">{{ $booking->booking_number }}</td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $booking->customer?->name ?? '—' }}</td>
                            <td class="px-6 py-3 text-luxury-muted">
                                <p class="text-xs">{{ $booking->driver?->name ?? '—' }}</p>
                                <p class="text-xs">{{ $booking->vehicle?->name ?? '—' }}</p>
                            </td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $booking->type_label }}</td>
                            <td class="px-6 py-3">
                                <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ match ($booking->status) {
                                    'completed' => 'bg-emerald-500/10 text-emerald-400',
                                    'cancelled' => 'bg-red-500/10 text-red-400',
                                    'assigned' => 'bg-luxury-gold/10 text-luxury-gold',
                                    'in_progress' => 'bg-blue-500/10 text-blue-400',
                                    'confirmed' => 'bg-luxury-secondary/10 text-luxury-secondary',
                                    default => 'bg-luxury-slate text-luxury-muted',
                                } }}">
                                    {{ $booking->status_label }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $booking->pickup_datetime->format('M d, Y H:i') }}</td>
                            <td class="px-6 py-3 text-end font-medium text-luxury-white">{{ currency($booking->fare_amount) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-luxury-muted">{{ __('No bookings found in this range.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Mobile: cards --}}
    <div class="space-y-3 sm:hidden">
        @forelse ($rows as $booking)
            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium text-luxury-white">{{ $booking->booking_number }}</p>
                        <p class="truncate text-xs text-luxury-muted">{{ $booking->customer?->name ?? '—' }} &middot; {{ $booking->type_label }}</p>
                    </div>
                    <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-medium {{ match ($booking->status) {
                        'completed' => 'bg-emerald-500/10 text-emerald-400',
                        'cancelled' => 'bg-red-500/10 text-red-400',
                        'assigned' => 'bg-luxury-gold/10 text-luxury-gold',
                        'in_progress' => 'bg-blue-500/10 text-blue-400',
                        'confirmed' => 'bg-luxury-secondary/10 text-luxury-secondary',
                        default => 'bg-luxury-slate text-luxury-muted',
                    } }}">
                        {{ $booking->status_label }}
                    </span>
                </div>

                <div class="mt-3 flex items-center justify-between gap-3 border-t border-luxury-border pt-3 text-xs">
                    <span class="text-luxury-muted">{{ $booking->driver?->name ?? '—' }} &middot; {{ $booking->vehicle?->name ?? '—' }}</span>
                    <span class="shrink-0 font-semibold text-luxury-white">{{ currency($booking->fare_amount) }}</span>
                </div>
                <p class="mt-1 text-[11px] text-luxury-muted">{{ $booking->pickup_datetime->format('M d, Y H:i') }}</p>
            </div>
        @empty
            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-10 text-center text-sm text-luxury-muted">
                {{ __('No bookings found in this range.') }}
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
