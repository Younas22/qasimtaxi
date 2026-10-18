<x-admin.layouts.app :title="__('Currencies')">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Currencies') }}</h2>
            <p class="mt-1 text-sm text-luxury-muted">{{ __('Manage supported currencies and exchange rates.') }}</p>
        </div>

        @permission('currencies.create')
            <a href="{{ route('admin.currencies.create') }}">
                <x-admin.button type="button" variant="primary">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    {{ __('Add Currency') }}
                </x-admin.button>
            </a>
        @endpermission
    </div>

    {{-- Desktop: table --}}
    <div class="hidden overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal sm:block">
        <div class="overflow-x-auto">
            <table class="w-full text-start text-sm">
                <thead>
                    <tr class="border-b border-luxury-border text-xs uppercase tracking-wider text-luxury-muted">
                        <th class="px-6 py-3 font-medium">{{ __('Currency') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Code') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Symbol') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Exchange Rate') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Status') }}</th>
                        <th class="px-6 py-3 text-end font-medium">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-luxury-border/60">
                    @forelse ($currencies as $currency)
                        <tr class="hover:bg-luxury-graphite">
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-luxury-white">{{ $currency->name }}</span>
                                    @if ($currency->is_default)
                                        <span class="rounded-full bg-luxury-gold/10 px-2 py-0.5 text-[10px] uppercase tracking-wide text-luxury-gold">{{ __('Default') }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $currency->code }}</td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $currency->symbol }}</td>
                            <td class="px-6 py-3 text-luxury-muted">
                                {{ number_format($currency->exchange_rate, 6) }}
                                @if ($currency->is_default)
                                    <span class="text-[11px] text-luxury-muted">{{ __('(base)') }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $currency->is_active ? 'bg-emerald-500/10 text-emerald-400' : 'bg-luxury-slate text-luxury-muted' }}">
                                    {{ $currency->is_active ? __('Active') : __('Disabled') }}
                                </span>
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    @permission('currencies.edit')
                                        <a href="{{ route('admin.currencies.edit', $currency) }}" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                            {{ __('Edit') }}
                                        </a>

                                        @unless ($currency->is_default)
                                            <form method="POST" action="{{ route('admin.currencies.default', $currency) }}">
                                                @csrf
                                                <button type="submit" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-secondary hover:text-luxury-secondary">
                                                    {{ __('Set Default') }}
                                                </button>
                                            </form>
                                        @endunless

                                        @unless ($currency->is_default)
                                            <form method="POST" action="{{ route('admin.currencies.toggle', $currency) }}">
                                                @csrf
                                                <button type="submit" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                                    {{ $currency->is_active ? __('Disable') : __('Enable') }}
                                                </button>
                                            </form>
                                        @endunless
                                    @endpermission

                                    @permission('currencies.delete')
                                        @unless ($currency->is_default)
                                            <form method="POST" action="{{ route('admin.currencies.destroy', $currency) }}" onsubmit="return confirm('{{ __('Delete this currency?') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-lg border border-red-500/30 px-3 py-1.5 text-xs font-medium text-red-400 transition hover:bg-red-500/10">
                                                    {{ __('Delete') }}
                                                </button>
                                            </form>
                                        @endunless
                                    @endpermission
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-luxury-muted">{{ __('No currencies configured yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Mobile: cards --}}
    <div class="space-y-3 sm:hidden">
        @forelse ($currencies as $currency)
            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="truncate text-sm font-medium text-luxury-white">{{ $currency->name }}</span>
                            @if ($currency->is_default)
                                <span class="shrink-0 rounded-full bg-luxury-gold/10 px-2 py-0.5 text-[10px] uppercase tracking-wide text-luxury-gold">{{ __('Default') }}</span>
                            @endif
                        </div>
                        <p class="text-xs text-luxury-muted">{{ $currency->code }} &middot; {{ $currency->symbol }}</p>
                    </div>
                    <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-medium {{ $currency->is_active ? 'bg-emerald-500/10 text-emerald-400' : 'bg-luxury-slate text-luxury-muted' }}">
                        {{ $currency->is_active ? __('Active') : __('Disabled') }}
                    </span>
                </div>

                <p class="mt-3 border-t border-luxury-border pt-3 text-xs text-luxury-muted">
                    {{ __('Exchange Rate') }}: <span class="font-medium text-luxury-white">{{ number_format($currency->exchange_rate, 6) }}</span>
                    @if ($currency->is_default) <span>({{ __('base') }})</span> @endif
                </p>

                <div class="mt-3 flex flex-wrap items-center gap-2 border-t border-luxury-border pt-3">
                    @permission('currencies.edit')
                        <a href="{{ route('admin.currencies.edit', $currency) }}" class="tap-scale rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                            {{ __('Edit') }}
                        </a>
                        @unless ($currency->is_default)
                            <form method="POST" action="{{ route('admin.currencies.default', $currency) }}">
                                @csrf
                                <button type="submit" class="tap-scale rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-secondary hover:text-luxury-secondary">
                                    {{ __('Set Default') }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.currencies.toggle', $currency) }}">
                                @csrf
                                <button type="submit" class="tap-scale rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                    {{ $currency->is_active ? __('Disable') : __('Enable') }}
                                </button>
                            </form>
                        @endunless
                    @endpermission
                    @permission('currencies.delete')
                        @unless ($currency->is_default)
                            <form method="POST" action="{{ route('admin.currencies.destroy', $currency) }}" onsubmit="return confirm('{{ __('Delete this currency?') }}');" class="ms-auto">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="tap-scale rounded-lg border border-red-500/30 px-3 py-1.5 text-xs font-medium text-red-400 transition hover:bg-red-500/10">
                                    {{ __('Delete') }}
                                </button>
                            </form>
                        @endunless
                    @endpermission
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-10 text-center text-sm text-luxury-muted">
                {{ __('No currencies configured yet.') }}
            </div>
        @endforelse
    </div>
</x-admin.layouts.app>
