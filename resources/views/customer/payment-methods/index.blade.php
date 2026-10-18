<x-customer.layouts.app :title="__('Payment Methods')">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Payment Methods') }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ __('Supported payment gateways and your payment history.') }}</p>
    </div>

    {{-- Supported Gateways --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @php
            $stripeReady = $gateways->get('stripe')?->isReady() ?? false;
            $paypalReady = $gateways->get('paypal')?->isReady() ?? false;
        @endphp

        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-5">
            <div class="flex items-center justify-between">
                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-luxury-gold/10 text-luxury-gold">
                    <x-icon name="credit-card" class="h-5 w-5" />
                </span>
                <span class="rounded-full px-2.5 py-1 text-[11px] font-medium {{ $stripeReady ? 'bg-emerald-500/10 text-emerald-400' : 'bg-luxury-slate text-luxury-muted' }}">
                    {{ $stripeReady ? __('Available') : __('Not Configured') }}
                </span>
            </div>
            <p class="mt-3 font-semibold text-luxury-white">{{ __('Stripe') }}</p>
            <p class="mt-0.5 text-xs text-luxury-muted">{{ __('Pay securely by card via Stripe.') }}</p>
        </div>

        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-5">
            <div class="flex items-center justify-between">
                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-luxury-gold/10 text-luxury-gold">
                    <x-icon name="credit-card" class="h-5 w-5" />
                </span>
                <span class="rounded-full px-2.5 py-1 text-[11px] font-medium {{ $paypalReady ? 'bg-emerald-500/10 text-emerald-400' : 'bg-luxury-slate text-luxury-muted' }}">
                    {{ $paypalReady ? __('Available') : __('Not Configured') }}
                </span>
            </div>
            <p class="mt-3 font-semibold text-luxury-white">{{ __('PayPal') }}</p>
            <p class="mt-0.5 text-xs text-luxury-muted">{{ __('Pay using your PayPal balance or card.') }}</p>
        </div>

        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-5 opacity-60">
            <div class="flex items-center justify-between">
                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-luxury-slate text-luxury-muted">
                    <x-icon name="credit-card" class="h-5 w-5" />
                </span>
                <span class="rounded-full bg-luxury-slate px-2.5 py-1 text-[11px] font-medium text-luxury-muted">{{ __('Soon') }}</span>
            </div>
            <p class="mt-3 font-semibold text-luxury-white">{{ __('Apple Pay') }}</p>
            <p class="mt-0.5 text-xs text-luxury-muted">{{ __('Coming soon.') }}</p>
        </div>

        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-5 opacity-60">
            <div class="flex items-center justify-between">
                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-luxury-slate text-luxury-muted">
                    <x-icon name="credit-card" class="h-5 w-5" />
                </span>
                <span class="rounded-full bg-luxury-slate px-2.5 py-1 text-[11px] font-medium text-luxury-muted">{{ __('Soon') }}</span>
            </div>
            <p class="mt-3 font-semibold text-luxury-white">{{ __('Google Pay') }}</p>
            <p class="mt-0.5 text-xs text-luxury-muted">{{ __('Coming soon.') }}</p>
        </div>
    </div>

    {{-- Payment History --}}
    <div class="mt-8">
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h3 class="text-sm font-semibold text-luxury-white">{{ __('Payment History') }}</h3>
            <form method="GET" class="flex items-center gap-2">
                <div class="relative flex-1 sm:flex-none">
                    <x-icon name="search" class="pointer-events-none absolute start-3 top-1/2 h-4 w-4 -translate-y-1/2 text-luxury-muted" />
                    <input type="search" name="search" value="{{ $search }}" placeholder="{{ __('Search by booking # or transaction ID...') }}"
                        class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal py-2 ps-9 pe-3 text-sm text-luxury-white placeholder:text-luxury-muted/70 focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold sm:w-64">
                </div>
                @if ($search)
                    <a href="{{ route('customer.payment-methods.index') }}" class="shrink-0 text-sm text-luxury-muted hover:text-luxury-white">{{ __('Clear') }}</a>
                @endif
            </form>
        </div>

        {{-- Desktop: table --}}
        <div class="hidden overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal sm:block">
            <div class="overflow-x-auto">
                <table class="w-full text-start text-sm">
                    <thead>
                        <tr class="border-b border-luxury-border text-xs uppercase tracking-wider text-luxury-muted">
                            <th class="px-6 py-3 font-medium">{{ __('Booking') }}</th>
                            <th class="px-6 py-3 font-medium">{{ __('Date') }}</th>
                            <th class="px-6 py-3 font-medium">{{ __('Gateway') }}</th>
                            <th class="px-6 py-3 font-medium">{{ __('Transaction ID') }}</th>
                            <th class="px-6 py-3 font-medium">{{ __('Amount') }}</th>
                            <th class="px-6 py-3 font-medium">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-luxury-border/60">
                        @forelse ($payments as $payment)
                            <tr class="hover:bg-luxury-graphite">
                                <td class="px-6 py-3 font-medium text-luxury-white">{{ $payment->booking_number }}</td>
                                <td class="px-6 py-3 text-luxury-muted">{{ ($payment->paid_at ?? $payment->updated_at)->format('M d, Y') }}</td>
                                <td class="px-6 py-3 text-luxury-muted">{{ $payment->payment_gateway_label ?? '—' }}</td>
                                <td class="px-6 py-3 font-mono text-xs text-luxury-muted">{{ $payment->transaction_id ?? '—' }}</td>
                                <td class="px-6 py-3 text-luxury-white">{{ currency($payment->fare_amount) }}</td>
                                <td class="px-6 py-3"><x-customer.payment-badge :status="$payment->payment_status" /></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-luxury-muted">
                                    {{ $search ? __('No payments match your search.') : __('No payment history yet.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Mobile: cards --}}
        <div class="space-y-3 sm:hidden">
            @forelse ($payments as $payment)
                <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-luxury-white">{{ $payment->booking_number }}</p>
                            <p class="mt-0.5 text-xs text-luxury-muted">{{ ($payment->paid_at ?? $payment->updated_at)->format('M d, Y') }} &middot; {{ $payment->payment_gateway_label ?? '—' }}</p>
                        </div>
                        <x-customer.payment-badge :status="$payment->payment_status" />
                    </div>
                    <div class="mt-3 flex items-center justify-between border-t border-luxury-border pt-3">
                        <span class="truncate font-mono text-[11px] text-luxury-muted">{{ $payment->transaction_id ?? '—' }}</span>
                        <span class="shrink-0 text-sm font-semibold text-luxury-white">{{ currency($payment->fare_amount) }}</span>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-10 text-center text-sm text-luxury-muted">
                    {{ $search ? __('No payments match your search.') : __('No payment history yet.') }}
                </div>
            @endforelse
        </div>

        @if ($payments->hasPages())
            <div class="mt-6 flex items-center justify-between text-sm text-luxury-muted">
                <div>
                    @if ($payments->onFirstPage())
                        <span class="cursor-not-allowed rounded-lg border border-luxury-border px-3 py-1.5 opacity-40">{{ __('Previous') }}</span>
                    @else
                        <a href="{{ $payments->previousPageUrl() }}" class="rounded-lg border border-luxury-border px-3 py-1.5 hover:border-luxury-gold/40 hover:text-luxury-gold">{{ __('Previous') }}</a>
                    @endif
                </div>
                <p>{{ __('Page :current of :last', ['current' => $payments->currentPage(), 'last' => $payments->lastPage()]) }}</p>
                <div>
                    @if ($payments->hasMorePages())
                        <a href="{{ $payments->nextPageUrl() }}" class="rounded-lg border border-luxury-border px-3 py-1.5 hover:border-luxury-gold/40 hover:text-luxury-gold">{{ __('Next') }}</a>
                    @else
                        <span class="cursor-not-allowed rounded-lg border border-luxury-border px-3 py-1.5 opacity-40">{{ __('Next') }}</span>
                    @endif
                </div>
            </div>
        @endif
    </div>
</x-customer.layouts.app>
