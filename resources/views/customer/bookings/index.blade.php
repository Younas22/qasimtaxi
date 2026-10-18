<x-customer.layouts.app :title="$heading">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ $heading }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ __('View, search, and filter your ride history.') }}</p>
    </div>

    @include('customer.bookings.partials.tabs', ['active' => 'all'])

    <form method="GET" class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="relative flex-1 sm:max-w-sm">
            <x-icon name="search" class="pointer-events-none absolute start-4 top-1/2 h-4 w-4 -translate-y-1/2 text-luxury-muted" />
            <input type="search" name="search" value="{{ $search }}" placeholder="{{ __('Search by booking ID, pickup, or drop-off...') }}"
                class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal py-2.5 ps-11 pe-4 text-sm text-luxury-white placeholder:text-luxury-muted/70 focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
        </div>

        @if ($showStatusFilter)
            <select name="status" onchange="this.form.submit()"
                class="rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2.5 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                <option value="">{{ __('All Statuses') }}</option>
                @foreach ($filterStatuses as $value => $label)
                    <option value="{{ $value }}" @selected($statusFilter === $value)>{{ __($label) }}</option>
                @endforeach
            </select>
        @endif

        <button type="submit" class="tap-scale inline-flex items-center justify-center gap-2 rounded-lg bg-luxury-gold px-4 py-2.5 text-sm font-semibold text-luxury-black transition hover:bg-luxury-gold-light">
            {{ __('Search') }}
        </button>

        @if ($search || $statusFilter)
            <a href="{{ url()->current() }}" class="text-sm text-luxury-muted hover:text-luxury-white">{{ __('Clear') }}</a>
        @endif
    </form>

    {{-- Desktop: table --}}
    <div class="hidden overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal sm:block">
        <div class="overflow-x-auto">
            <table class="w-full text-start text-sm">
                <thead>
                    <tr class="border-b border-luxury-border text-xs uppercase tracking-wider text-luxury-muted">
                        <th class="px-6 py-3 font-medium">{{ __('Booking ID') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Pickup') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Drop-off') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Vehicle') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Driver') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Price') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Status') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Payment') }}</th>
                        <th class="px-6 py-3 text-end font-medium">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-luxury-border/60">
                    @forelse ($bookings as $booking)
                        <tr class="hover:bg-luxury-graphite">
                            <td class="px-6 py-3 font-medium text-luxury-white">{{ $booking->booking_number }}</td>
                            <td class="max-w-[160px] px-6 py-3 text-luxury-muted">
                                <p class="truncate">{{ $booking->pickup_location }}</p>
                            </td>
                            <td class="max-w-[160px] px-6 py-3 text-luxury-muted">
                                <p class="truncate">{{ $booking->dropoff_location }}</p>
                            </td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $booking->vehicle?->category?->name ?? $booking->vehicle?->name ?? '—' }}</td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $booking->driver?->name ?? '—' }}</td>
                            <td class="px-6 py-3 text-luxury-muted">{{ currency($booking->fare_amount) }}</td>
                            <td class="px-6 py-3"><x-customer.status-badge :status="$booking->status" /></td>
                            <td class="px-6 py-3"><x-customer.payment-badge :status="$booking->payment_status" /></td>
                            <td class="px-6 py-3">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('customer.bookings.show', $booking) }}" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                        {{ __('View') }}
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-10 text-center text-luxury-muted">
                                {{ ($search || $statusFilter) ? __('No bookings match your search.') : __('No bookings found.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Mobile: stacked cards --}}
    <div class="space-y-4 sm:hidden">
        @forelse ($bookings as $booking)
            <a href="{{ route('customer.bookings.show', $booking) }}"
                class="tap-scale block overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal">
                <div class="flex items-center justify-between gap-2 border-b border-luxury-border bg-luxury-graphite/40 px-4 py-3">
                    <span class="text-xs font-medium text-luxury-white">{{ $booking->booking_number }}</span>
                    <x-customer.status-badge :status="$booking->status" />
                </div>

                <div class="space-y-3 p-4">
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5 flex flex-col items-center">
                            <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-luxury-gold"></span>
                            <span class="my-1 h-6 w-px bg-luxury-border"></span>
                            <x-icon name="map-pin" class="h-3 w-3 shrink-0 text-luxury-gold" />
                        </div>
                        <div class="min-w-0 flex-1 space-y-2">
                            <p class="truncate text-sm font-medium text-luxury-white">{{ $booking->pickup_location }}</p>
                            <p class="truncate text-sm font-medium text-luxury-white">{{ $booking->dropoff_location }}</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-3 border-t border-luxury-border pt-3 text-xs text-luxury-muted">
                        <span class="truncate">{{ $booking->vehicle?->category?->name ?? $booking->vehicle?->name ?? '—' }}</span>
                        <span class="shrink-0 truncate">{{ $booking->driver?->name ?? __('No driver yet') }}</span>
                    </div>

                    <div class="flex items-center justify-between gap-3 border-t border-luxury-border pt-3">
                        <span class="text-base font-semibold text-luxury-gold">{{ currency($booking->fare_amount) }}</span>
                        <x-customer.payment-badge :status="$booking->payment_status" />
                    </div>
                </div>
            </a>
        @empty
            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-10 text-center text-sm text-luxury-muted">
                {{ ($search || $statusFilter) ? __('No bookings match your search.') : __('No bookings found.') }}
            </div>
        @endforelse
    </div>

    @if ($bookings->hasPages())
        <div class="mt-6 flex items-center justify-between text-sm text-luxury-muted">
            <div>
                @if ($bookings->onFirstPage())
                    <span class="cursor-not-allowed rounded-lg border border-luxury-border px-3 py-1.5 opacity-40">{{ __('Previous') }}</span>
                @else
                    <a href="{{ $bookings->previousPageUrl() }}" class="rounded-lg border border-luxury-border px-3 py-1.5 hover:border-luxury-gold/40 hover:text-luxury-gold">{{ __('Previous') }}</a>
                @endif
            </div>
            <p>{{ __('Page :current of :last', ['current' => $bookings->currentPage(), 'last' => $bookings->lastPage()]) }}</p>
            <div>
                @if ($bookings->hasMorePages())
                    <a href="{{ $bookings->nextPageUrl() }}" class="rounded-lg border border-luxury-border px-3 py-1.5 hover:border-luxury-gold/40 hover:text-luxury-gold">{{ __('Next') }}</a>
                @else
                    <span class="cursor-not-allowed rounded-lg border border-luxury-border px-3 py-1.5 opacity-40">{{ __('Next') }}</span>
                @endif
            </div>
        </div>
    @endif
</x-customer.layouts.app>
