<x-driver.layouts.app :title="__('My Rides')">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-luxury-white">{{ __('My Rides') }}</h2>
            <p class="mt-1 text-sm text-luxury-muted">{{ __('All trips assigned to you.') }}</p>
        </div>

        <form method="GET">
            <select name="status" onchange="this.form.submit()"
                class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2.5 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold sm:w-auto">
                <option value="">{{ __('All Statuses') }}</option>
                @foreach ($filterStatuses as $value => $label)
                    <option value="{{ $value }}" @selected($statusFilter === $value)>{{ __($label) }}</option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- Desktop: table --}}
    <div class="hidden overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal sm:block">
        <div class="overflow-x-auto">
            <table class="w-full text-start text-sm">
                <thead>
                    <tr class="border-b border-luxury-border text-xs uppercase tracking-wider text-luxury-muted">
                        <th class="px-6 py-3 font-medium">{{ __('Booking') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Customer') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Route') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Date') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Status') }}</th>
                        <th class="px-6 py-3 text-end font-medium">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-luxury-border/60">
                    @forelse ($bookings as $booking)
                        <tr class="hover:bg-luxury-graphite">
                            <td class="px-6 py-3 font-medium text-luxury-white">{{ $booking->booking_number }}</td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $booking->customer?->name ?? '—' }}</td>
                            <td class="max-w-xs px-6 py-3 text-luxury-muted">
                                <p class="truncate">{{ $booking->pickup_location }} &rarr; {{ $booking->dropoff_location }}</p>
                            </td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $booking->pickup_datetime->format('M d, Y') }}</td>
                            <td class="px-6 py-3"><x-customer.status-badge :status="$booking->status" /></td>
                            <td class="px-6 py-3">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('driver.bookings.show', $booking) }}" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                        {{ __('View') }}
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-luxury-muted">{{ __('No bookings found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Mobile: cards --}}
    <div class="space-y-3 sm:hidden">
        @forelse ($bookings as $booking)
            <a href="{{ route('driver.bookings.show', $booking) }}"
                class="tap-scale block rounded-2xl border border-luxury-border bg-luxury-charcoal p-4 transition hover:border-luxury-gold/40">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium text-luxury-white">{{ $booking->customer?->name ?? $booking->booking_number }}</p>
                        <p class="mt-0.5 text-xs text-luxury-muted">{{ $booking->booking_number }} &middot; {{ $booking->pickup_datetime->format('M d, Y — h:i A') }}</p>
                    </div>
                    <x-customer.status-badge :status="$booking->status" />
                </div>
                <div class="mt-3 flex items-start gap-2 border-t border-luxury-border pt-3">
                    <x-icon name="map-pin" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-luxury-gold" />
                    <p class="min-w-0 flex-1 truncate text-xs text-luxury-muted">{{ $booking->pickup_location }} &rarr; {{ $booking->dropoff_location }}</p>
                </div>
            </a>
        @empty
            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-10 text-center text-sm text-luxury-muted">
                {{ __('No bookings found.') }}
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
</x-driver.layouts.app>
