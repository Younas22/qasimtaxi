<x-customer.layouts.app :title="__('Invoices')">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Invoices') }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ __('Download invoices for your bookings.') }}</p>
    </div>

    {{-- Desktop: table --}}
    <div class="hidden overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal sm:block">
        <div class="overflow-x-auto">
            <table class="w-full text-start text-sm">
                <thead>
                    <tr class="border-b border-luxury-border text-xs uppercase tracking-wider text-luxury-muted">
                        <th class="px-6 py-3 font-medium">{{ __('Invoice #') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Booking') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Date') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Amount') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Payment') }}</th>
                        <th class="px-6 py-3 text-end font-medium">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-luxury-border/60">
                    @forelse ($bookings as $booking)
                        <tr class="hover:bg-luxury-graphite">
                            <td class="px-6 py-3 font-medium text-luxury-white">{{ $booking->invoice_number }}</td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $booking->booking_number }}</td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $booking->pickup_datetime->format('M d, Y') }}</td>
                            <td class="px-6 py-3 text-luxury-muted">{{ currency($booking->fare_amount) }}</td>
                            <td class="px-6 py-3"><x-customer.payment-badge :status="$booking->payment_status" /></td>
                            <td class="px-6 py-3 text-end">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('booking.invoice', $booking->booking_number) }}" target="_blank"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                        <x-icon name="eye" class="h-3.5 w-3.5" />
                                        {{ __('View') }}
                                    </a>
                                    <a href="{{ route('booking.invoice.download', $booking->booking_number) }}"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                        <x-icon name="download" class="h-3.5 w-3.5" />
                                        {{ __('PDF') }}
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-luxury-muted">{{ __('No invoices available yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Mobile: cards --}}
    <div class="space-y-3 sm:hidden">
        @forelse ($bookings as $booking)
            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium text-luxury-white">{{ $booking->invoice_number }}</p>
                        <p class="mt-0.5 text-xs text-luxury-muted">{{ $booking->booking_number }} &middot; {{ $booking->pickup_datetime->format('M d, Y') }}</p>
                    </div>
                    <x-customer.payment-badge :status="$booking->payment_status" />
                </div>
                <div class="mt-3 flex items-center justify-between gap-3 border-t border-luxury-border pt-3">
                    <span class="text-base font-semibold text-luxury-gold">{{ currency($booking->fare_amount) }}</span>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('booking.invoice', $booking->booking_number) }}" target="_blank"
                            class="tap-scale inline-flex items-center gap-1.5 rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                            <x-icon name="eye" class="h-3.5 w-3.5" />
                            {{ __('View') }}
                        </a>
                        <a href="{{ route('booking.invoice.download', $booking->booking_number) }}"
                            class="tap-scale inline-flex items-center gap-1.5 rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                            <x-icon name="download" class="h-3.5 w-3.5" />
                            {{ __('PDF') }}
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-10 text-center text-sm text-luxury-muted">
                {{ __('No invoices available yet.') }}
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
