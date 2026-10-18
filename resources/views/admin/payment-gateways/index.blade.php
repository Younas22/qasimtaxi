<x-admin.layouts.app :title="__('Payment Gateways')">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Payment Gateways') }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ __('Manage Stripe and PayPal credentials, sandbox/live mode, and availability.') }}</p>
    </div>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        @foreach ($gateways as $gateway)
            <div class="space-y-4 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        @if ($gateway->code === 'stripe')
                            <div class="h-11 w-11 shrink-0 overflow-hidden rounded-xl border border-luxury-border">
                                <img src="{{ asset('images/payments/stripe-mark.png') }}" alt="Stripe" class="h-full w-full object-cover">
                            </div>
                        @elseif ($gateway->code === 'paypal')
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-luxury-border bg-white">
                                <img src="{{ asset('images/payments/paypal-mark.svg') }}" alt="PayPal" class="h-6 w-6 object-contain">
                            </div>
                        @else
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-luxury-border bg-luxury-graphite">
                                <svg class="h-5 w-5 text-luxury-gold" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 8.25v10.5a1.5 1.5 0 001.5 1.5h16.5a1.5 1.5 0 001.5-1.5V8.25M2.25 8.25v-1.5a1.5 1.5 0 011.5-1.5h16.5a1.5 1.5 0 011.5 1.5v1.5M6 15.75h4.5" />
                                </svg>
                            </div>
                        @endif
                        <div>
                            <p class="font-semibold text-luxury-white">{{ $gateway->name }}</p>
                            <div class="mt-1 flex flex-wrap items-center gap-1.5">
                                <span class="rounded-full px-2 py-0.5 text-[11px] font-medium uppercase tracking-wide {{ $gateway->is_enabled ? 'bg-emerald-500/10 text-emerald-400' : 'bg-luxury-slate text-luxury-muted' }}">
                                    {{ $gateway->is_enabled ? __('Enabled') : __('Disabled') }}
                                </span>
                                <span class="rounded-full px-2 py-0.5 text-[11px] font-medium uppercase tracking-wide {{ $gateway->isLive() ? 'bg-red-500/10 text-red-400' : 'bg-luxury-secondary/10 text-luxury-secondary' }}">
                                    {{ $gateway->isLive() ? __('Live Mode') : __('Sandbox Mode') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <p class="text-xs text-luxury-muted">
                    @if ($gateway->hasActiveKeys())
                        {{ __(':key1 and :key2 configured for :mode mode.', ['key1' => $gateway->config()['key_1_label'] ?? __('Key'), 'key2' => $gateway->config()['key_2_label'] ?? __('Secret'), 'mode' => $gateway->mode]) }}
                    @else
                        {{ __('No :mode credentials configured yet.', ['mode' => $gateway->mode]) }}
                    @endif
                </p>

                <div class="flex flex-wrap items-center gap-2">
                    @permission('payments.edit')
                        <a href="{{ route('admin.payment-gateways.edit', $gateway) }}" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                            {{ __('Configure') }}
                        </a>
                        <form method="POST" action="{{ route('admin.payment-gateways.toggle', $gateway) }}">
                            @csrf
                            <button type="submit" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                {{ $gateway->is_enabled ? __('Disable') : __('Enable') }}
                            </button>
                        </form>
                    @endpermission
                </div>
            </div>
        @endforeach
    </div>
</x-admin.layouts.app>
