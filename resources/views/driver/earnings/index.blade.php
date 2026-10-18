<x-driver.layouts.app :title="__('Earnings')">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Earnings') }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ __('Commission rate: :rate%', ['rate' => (float) $commissionRate]) }}</p>
    </div>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
        <x-admin.stat-card :label="__('This Month')" :value="currency($stats['thisMonth'])">
            <x-icon name="cash" class="h-5 w-5" />
        </x-admin.stat-card>

        <x-admin.stat-card :label="__('Last Month')" :value="currency($stats['lastMonth'])">
            <x-icon name="calendar" class="h-5 w-5" />
        </x-admin.stat-card>

        <x-admin.stat-card :label="__('All-Time Total')" :value="currency($stats['total'])">
            <x-icon name="trending-up" class="h-5 w-5" />
        </x-admin.stat-card>
    </div>

    <div class="mt-8 overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal">
        <div class="border-b border-luxury-border px-6 py-4">
            <h3 class="text-sm font-semibold text-luxury-white">{{ __('Paid Trips') }}</h3>
        </div>
        {{-- Desktop: table --}}
        <div class="hidden overflow-x-auto sm:block">
            <table class="w-full text-start text-sm">
                <thead>
                    <tr class="border-b border-luxury-border text-xs uppercase tracking-wider text-luxury-muted">
                        <th class="px-6 py-3 font-medium">{{ __('Booking') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Date') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Fare') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Your Earnings') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-luxury-border/60">
                    @forelse ($bookings as $booking)
                        <tr class="hover:bg-luxury-graphite">
                            <td class="px-6 py-3 font-medium text-luxury-white">{{ $booking->booking_number }}</td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $booking->pickup_datetime->format('M d, Y') }}</td>
                            <td class="px-6 py-3 text-luxury-muted">{{ currency($booking->fare_amount) }}</td>
                            <td class="px-6 py-3 font-medium text-luxury-gold">{{ currency($booking->fare_amount * ($commissionRate / 100)) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-luxury-muted">{{ __('No paid trips yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile: cards --}}
        <div class="divide-y divide-luxury-border/60 sm:hidden">
            @forelse ($bookings as $booking)
                <div class="flex items-center justify-between gap-3 p-4">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium text-luxury-white">{{ $booking->booking_number }}</p>
                        <p class="mt-0.5 text-xs text-luxury-muted">{{ $booking->pickup_datetime->format('M d, Y') }} &middot; {{ __('Fare') }}: {{ currency($booking->fare_amount) }}</p>
                    </div>
                    <span class="shrink-0 text-base font-semibold text-luxury-gold">{{ currency($booking->fare_amount * ($commissionRate / 100)) }}</span>
                </div>
            @empty
                <p class="px-4 py-10 text-center text-sm text-luxury-muted">{{ __('No paid trips yet.') }}</p>
            @endforelse
        </div>
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
