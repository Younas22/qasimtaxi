@php
    $activeCurrencies = \App\Models\Currency::active();
    $currentCurrency = active_currency();
@endphp

@if ($currentCurrency && $activeCurrencies->count() > 1)
    <div x-data="{ open: false }" class="relative">
        <button @click="open = !open" @click.outside="open = false"
            class="flex h-10 items-center gap-1.5 rounded-lg border border-luxury-border px-3 text-sm text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
            <span class="font-semibold text-luxury-white">{{ $currentCurrency->symbol }}</span>
            <span class="hidden sm:inline">{{ $currentCurrency->code }}</span>
        </button>

        <div x-show="open" x-cloak x-transition
            class="absolute end-0 z-30 mt-2 w-52 overflow-hidden rounded-xl border border-luxury-border bg-luxury-charcoal shadow-xl">
            @foreach ($activeCurrencies as $currency)
                <form method="POST" action="{{ route('admin.currency.switch', $currency->code) }}">
                    @csrf
                    <button type="submit"
                        class="flex w-full items-center gap-3 px-4 py-2.5 text-start text-sm transition hover:bg-luxury-graphite {{ $currency->code === $currentCurrency->code ? 'text-luxury-gold' : 'text-luxury-muted' }}">
                        <span class="w-5 shrink-0 text-center font-semibold">{{ $currency->symbol }}</span>
                        <span>{{ $currency->name }}</span>
                        <span class="text-xs text-luxury-muted">{{ $currency->code }}</span>
                        @if ($currency->code === $currentCurrency->code)
                            <svg class="ms-auto h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                        @endif
                    </button>
                </form>
            @endforeach
        </div>
    </div>
@endif
