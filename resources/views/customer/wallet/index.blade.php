<x-customer.layouts.app :title="__('Wallet')">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Wallet') }}</h2>
            <p class="mt-1 text-sm text-luxury-muted">{{ __('Track your balance, loyalty points, and transaction history.') }}</p>
        </div>

        <div x-data="{ open: false, amount: null }">
            <button type="button" @click="open = true"
                class="tap-scale inline-flex items-center gap-2 rounded-lg bg-luxury-gold px-4 py-2.5 text-sm font-semibold text-luxury-black transition hover:bg-luxury-gold-light">
                <x-icon name="plus" class="h-4 w-4" />
                {{ __('Add Money') }}
            </button>

            <div x-show="open" x-cloak
                class="fixed inset-0 z-[70] flex items-end justify-center bg-black/70 px-4 pb-4 sm:items-center sm:pb-0"
                x-transition.opacity>
                <div @click.outside="open = false"
                    class="w-full max-w-sm rounded-t-2xl border border-luxury-border bg-luxury-charcoal p-6 sm:rounded-2xl">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-luxury-white">{{ __('Add Money to Wallet') }}</h3>
                        <button type="button" @click="open = false" class="text-luxury-muted hover:text-luxury-white">
                            <x-icon name="close" class="h-5 w-5" />
                        </button>
                    </div>

                    <form method="POST" action="{{ route('customer.wallet.recharge') }}">
                        @csrf

                        <div class="grid grid-cols-4 gap-2">
                            @foreach (\App\Http\Controllers\Customer\WalletController::QUICK_AMOUNTS as $quick)
                                <button type="button" @click="amount = {{ $quick }}"
                                    :class="amount === {{ $quick }} ? 'border-luxury-gold bg-luxury-gold/10 text-luxury-gold' : 'border-luxury-border text-luxury-muted hover:border-luxury-gold/40'"
                                    class="tap-scale rounded-lg border px-2 py-2 text-sm font-medium transition">
                                    {{ currency($quick) }}
                                </button>
                            @endforeach
                        </div>

                        <div class="mt-4">
                            <x-admin.input-label for="amount" :value="__('Custom Amount')" />
                            <input type="number" id="amount" name="amount" x-model.number="amount" step="0.01"
                                min="{{ \App\Http\Controllers\Customer\WalletController::MIN_RECHARGE }}"
                                max="{{ \App\Http\Controllers\Customer\WalletController::MAX_RECHARGE }}"
                                placeholder="{{ __('Enter amount') }}"
                                class="mt-1 w-full rounded-lg border border-luxury-border bg-luxury-graphite px-3 py-2.5 text-sm text-luxury-white placeholder:text-luxury-muted/70 focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                            <x-admin.input-error :messages="$errors->get('amount')" class="mt-2" />
                            <p class="mt-1.5 text-xs text-luxury-muted">
                                {{ __('Min :min — Max :max', ['min' => currency(\App\Http\Controllers\Customer\WalletController::MIN_RECHARGE), 'max' => currency(\App\Http\Controllers\Customer\WalletController::MAX_RECHARGE)]) }}
                            </p>
                        </div>

                        <button type="submit" :disabled="!amount || amount <= 0"
                            class="tap-scale mt-5 flex w-full items-center justify-center gap-2 rounded-lg bg-luxury-gold px-4 py-2.5 text-sm font-semibold text-luxury-black transition hover:bg-luxury-gold-light disabled:cursor-not-allowed disabled:opacity-40">
                            <x-icon name="wallet" class="h-4 w-4" />
                            {{ __('Recharge Wallet') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-luxury-gold/30 bg-gradient-to-br from-luxury-charcoal to-luxury-graphite p-6">
            <p class="text-xs font-medium uppercase tracking-wider text-luxury-muted">{{ __('Wallet Balance') }}</p>
            <p class="mt-3 text-3xl font-semibold text-luxury-gold">{{ currency($balance) }}</p>
        </div>
        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
            <p class="flex items-center gap-1.5 text-xs font-medium uppercase tracking-wider text-luxury-muted">
                <x-icon name="arrow-down" class="h-3.5 w-3.5 text-emerald-400" />
                {{ __('Total Credited') }}
            </p>
            <p class="mt-3 text-3xl font-semibold text-emerald-400">{{ currency($totalCredited) }}</p>
        </div>
        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
            <p class="flex items-center gap-1.5 text-xs font-medium uppercase tracking-wider text-luxury-muted">
                <x-icon name="arrow-up" class="h-3.5 w-3.5 text-red-400" />
                {{ __('Total Debited') }}
            </p>
            <p class="mt-3 text-3xl font-semibold text-red-400">{{ currency($totalDebited) }}</p>
        </div>
        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
            <p class="text-xs font-medium uppercase tracking-wider text-luxury-muted">{{ __('Loyalty Points') }}</p>
            <p class="mt-3 text-3xl font-semibold text-luxury-white">{{ number_format($loyaltyPoints) }}</p>
        </div>
    </div>

    <div class="mt-8 overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal">
        <div class="flex flex-col gap-3 border-b border-luxury-border px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
            <h3 class="text-sm font-semibold text-luxury-white">{{ __('Transaction History') }}</h3>
            <div class="flex items-center gap-1.5">
                <a href="{{ route('customer.wallet.index') }}"
                    class="rounded-full px-3 py-1.5 text-xs font-medium transition {{ $activeType ? 'text-luxury-muted hover:text-luxury-white' : 'bg-luxury-gold/10 text-luxury-gold' }}">
                    {{ __('All') }}
                </a>
                <a href="{{ route('customer.wallet.index', ['type' => 'credit']) }}"
                    class="rounded-full px-3 py-1.5 text-xs font-medium transition {{ $activeType === 'credit' ? 'bg-emerald-500/10 text-emerald-400' : 'text-luxury-muted hover:text-luxury-white' }}">
                    {{ __('Credits') }}
                </a>
                <a href="{{ route('customer.wallet.index', ['type' => 'debit']) }}"
                    class="rounded-full px-3 py-1.5 text-xs font-medium transition {{ $activeType === 'debit' ? 'bg-red-500/10 text-red-400' : 'text-luxury-muted hover:text-luxury-white' }}">
                    {{ __('Debits') }}
                </a>
            </div>
        </div>
        {{-- Desktop: table --}}
        <div class="hidden overflow-x-auto sm:block">
            <table class="w-full text-start text-sm">
                <thead>
                    <tr class="border-b border-luxury-border text-xs uppercase tracking-wider text-luxury-muted">
                        <th class="px-6 py-3 font-medium">{{ __('Date') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Reason') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Type') }}</th>
                        <th class="px-6 py-3 text-end font-medium">{{ __('Amount') }}</th>
                        <th class="px-6 py-3 text-end font-medium">{{ __('Balance After') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-luxury-border/60">
                    @forelse ($transactions as $transaction)
                        <tr class="hover:bg-luxury-graphite">
                            <td class="px-6 py-3 text-luxury-muted">{{ $transaction->created_at->format('M d, Y — h:i A') }}</td>
                            <td class="px-6 py-3 text-luxury-white">{{ $transaction->reason ?? '—' }}</td>
                            <td class="px-6 py-3">
                                <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $transaction->type === 'credit' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-red-500/10 text-red-400' }}">
                                    {{ ucfirst($transaction->type) }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-end font-medium {{ $transaction->type === 'credit' ? 'text-emerald-400' : 'text-red-400' }}">
                                {{ $transaction->type === 'credit' ? '+' : '-' }}{{ currency($transaction->amount) }}
                            </td>
                            <td class="px-6 py-3 text-end text-luxury-muted">{{ currency($transaction->balance_after) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-luxury-muted">{{ __('No transactions yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile: cards --}}
        <div class="divide-y divide-luxury-border/60 sm:hidden">
            @forelse ($transactions as $transaction)
                <div class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-luxury-white">{{ $transaction->reason ?? __('Transaction') }}</p>
                            <p class="mt-0.5 text-xs text-luxury-muted">{{ $transaction->created_at->format('M d, Y — h:i A') }}</p>
                        </div>
                        <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-medium {{ $transaction->type === 'credit' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-red-500/10 text-red-400' }}">
                            {{ ucfirst($transaction->type) }}
                        </span>
                    </div>
                    <div class="mt-3 flex items-center justify-between border-t border-luxury-border pt-3 text-sm">
                        <span class="font-semibold {{ $transaction->type === 'credit' ? 'text-emerald-400' : 'text-red-400' }}">
                            {{ $transaction->type === 'credit' ? '+' : '-' }}{{ currency($transaction->amount) }}
                        </span>
                        <span class="text-xs text-luxury-muted">{{ __('Balance') }}: {{ currency($transaction->balance_after) }}</span>
                    </div>
                </div>
            @empty
                <p class="px-4 py-10 text-center text-sm text-luxury-muted">{{ __('No transactions yet.') }}</p>
            @endforelse
        </div>
    </div>

    @if ($transactions->hasPages())
        <div class="mt-6 flex items-center justify-between text-sm text-luxury-muted">
            <div>
                @if ($transactions->onFirstPage())
                    <span class="cursor-not-allowed rounded-lg border border-luxury-border px-3 py-1.5 opacity-40">{{ __('Previous') }}</span>
                @else
                    <a href="{{ $transactions->previousPageUrl() }}" class="rounded-lg border border-luxury-border px-3 py-1.5 hover:border-luxury-gold/40 hover:text-luxury-gold">{{ __('Previous') }}</a>
                @endif
            </div>
            <p>{{ __('Page :current of :last', ['current' => $transactions->currentPage(), 'last' => $transactions->lastPage()]) }}</p>
            <div>
                @if ($transactions->hasMorePages())
                    <a href="{{ $transactions->nextPageUrl() }}" class="rounded-lg border border-luxury-border px-3 py-1.5 hover:border-luxury-gold/40 hover:text-luxury-gold">{{ __('Next') }}</a>
                @else
                    <span class="cursor-not-allowed rounded-lg border border-luxury-border px-3 py-1.5 opacity-40">{{ __('Next') }}</span>
                @endif
            </div>
        </div>
    @endif
</x-customer.layouts.app>
