<x-admin.layouts.app :title="__('Coupons')">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Coupons') }}</h2>
            <p class="mt-1 text-sm text-luxury-muted">{{ __('Discount codes shown to customers on their dashboard.') }}</p>
        </div>

        @permission('coupons.create')
            <a href="{{ route('admin.coupons.create') }}">
                <x-admin.button type="button" variant="primary">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    {{ __('Add Coupon') }}
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
                        <th class="px-6 py-3 font-medium">{{ __('Code') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Discount') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Usage') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Expires') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Status') }}</th>
                        <th class="px-6 py-3 text-end font-medium">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-luxury-border/60">
                    @forelse ($coupons as $coupon)
                        <tr class="hover:bg-luxury-graphite">
                            <td class="px-6 py-3">
                                <p class="font-medium text-luxury-white">{{ $coupon->code }}</p>
                                <p class="text-xs text-luxury-muted">{{ $coupon->description }}</p>
                            </td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $coupon->discount_label }}</td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $coupon->used_count }} / {{ $coupon->usage_limit ?? '∞' }}</td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $coupon->expires_at?->format('M d, Y') ?? __('Never') }}</td>
                            <td class="px-6 py-3">
                                @if (! $coupon->is_active)
                                    <span class="rounded-full bg-luxury-slate px-2.5 py-1 text-xs font-medium text-luxury-muted">{{ __('Inactive') }}</span>
                                @elseif ($coupon->isExpired())
                                    <span class="rounded-full bg-red-500/10 px-2.5 py-1 text-xs font-medium text-red-400">{{ __('Expired') }}</span>
                                @elseif ($coupon->isExhausted())
                                    <span class="rounded-full bg-red-500/10 px-2.5 py-1 text-xs font-medium text-red-400">{{ __('Exhausted') }}</span>
                                @else
                                    <span class="rounded-full bg-emerald-500/10 px-2.5 py-1 text-xs font-medium text-emerald-400">{{ __('Active') }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    @permission('coupons.edit')
                                        <a href="{{ route('admin.coupons.edit', $coupon) }}" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                            {{ __('Edit') }}
                                        </a>
                                    @endpermission
                                    @permission('coupons.delete')
                                        <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}" onsubmit="return confirm('{{ __('Delete this coupon?') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg border border-red-500/30 px-3 py-1.5 text-xs font-medium text-red-400 transition hover:bg-red-500/10">
                                                {{ __('Delete') }}
                                            </button>
                                        </form>
                                    @endpermission
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-luxury-muted">{{ __('No coupons created yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Mobile: cards --}}
    <div class="space-y-3 sm:hidden">
        @forelse ($coupons as $coupon)
            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium text-luxury-white">{{ $coupon->code }}</p>
                        <p class="truncate text-xs text-luxury-muted">{{ $coupon->description }}</p>
                    </div>
                    @if (! $coupon->is_active)
                        <span class="shrink-0 rounded-full bg-luxury-slate px-2.5 py-1 text-xs font-medium text-luxury-muted">{{ __('Inactive') }}</span>
                    @elseif ($coupon->isExpired())
                        <span class="shrink-0 rounded-full bg-red-500/10 px-2.5 py-1 text-xs font-medium text-red-400">{{ __('Expired') }}</span>
                    @elseif ($coupon->isExhausted())
                        <span class="shrink-0 rounded-full bg-red-500/10 px-2.5 py-1 text-xs font-medium text-red-400">{{ __('Exhausted') }}</span>
                    @else
                        <span class="shrink-0 rounded-full bg-emerald-500/10 px-2.5 py-1 text-xs font-medium text-emerald-400">{{ __('Active') }}</span>
                    @endif
                </div>

                <div class="mt-3 grid grid-cols-2 gap-3 border-t border-luxury-border pt-3 text-xs">
                    <div>
                        <p class="text-luxury-muted">{{ __('Discount') }}</p>
                        <p class="mt-0.5 font-medium text-luxury-white">{{ $coupon->discount_label }}</p>
                    </div>
                    <div>
                        <p class="text-luxury-muted">{{ __('Usage') }}</p>
                        <p class="mt-0.5 font-medium text-luxury-white">{{ $coupon->used_count }} / {{ $coupon->usage_limit ?? '∞' }}</p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-luxury-muted">{{ __('Expires') }}</p>
                        <p class="mt-0.5 font-medium text-luxury-white">{{ $coupon->expires_at?->format('M d, Y') ?? __('Never') }}</p>
                    </div>
                </div>

                <div class="mt-3 flex flex-wrap items-center gap-2 border-t border-luxury-border pt-3">
                    @permission('coupons.edit')
                        <a href="{{ route('admin.coupons.edit', $coupon) }}" class="tap-scale rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                            {{ __('Edit') }}
                        </a>
                    @endpermission
                    @permission('coupons.delete')
                        <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}" onsubmit="return confirm('{{ __('Delete this coupon?') }}');" class="ms-auto">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="tap-scale rounded-lg border border-red-500/30 px-3 py-1.5 text-xs font-medium text-red-400 transition hover:bg-red-500/10">
                                {{ __('Delete') }}
                            </button>
                        </form>
                    @endpermission
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-10 text-center text-sm text-luxury-muted">
                {{ __('No coupons created yet.') }}
            </div>
        @endforelse
    </div>

    @if ($coupons->hasPages())
        <div class="mt-6 flex items-center justify-between text-sm text-luxury-muted">
            <div>
                @if ($coupons->onFirstPage())
                    <span class="cursor-not-allowed rounded-lg border border-luxury-border px-3 py-1.5 opacity-40">{{ __('Previous') }}</span>
                @else
                    <a href="{{ $coupons->previousPageUrl() }}" class="rounded-lg border border-luxury-border px-3 py-1.5 hover:border-luxury-gold/40 hover:text-luxury-gold">{{ __('Previous') }}</a>
                @endif
            </div>
            <p>{{ __('Page :current of :last', ['current' => $coupons->currentPage(), 'last' => $coupons->lastPage()]) }}</p>
            <div>
                @if ($coupons->hasMorePages())
                    <a href="{{ $coupons->nextPageUrl() }}" class="rounded-lg border border-luxury-border px-3 py-1.5 hover:border-luxury-gold/40 hover:text-luxury-gold">{{ __('Next') }}</a>
                @else
                    <span class="cursor-not-allowed rounded-lg border border-luxury-border px-3 py-1.5 opacity-40">{{ __('Next') }}</span>
                @endif
            </div>
        </div>
    @endif
</x-admin.layouts.app>
