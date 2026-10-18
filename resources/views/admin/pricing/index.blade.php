<x-admin.layouts.app :title="__('Dynamic Pricing')">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Dynamic Pricing Engine') }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ __('Control base fare, KM/hour rates, waiting, night, weekend, toll, airport and service charges per fleet category.') }}</p>
    </div>

    {{-- Global Default --}}
    <div class="mb-6 overflow-hidden rounded-2xl border border-luxury-gold/30 bg-luxury-charcoal">
        <div class="flex flex-col gap-4 p-6 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <span class="rounded-full bg-luxury-gold/10 px-2.5 py-1 text-xs font-medium text-luxury-gold">{{ __('Global Default') }}</span>
                </div>
                <p class="mt-2 text-sm text-luxury-muted">
                    {{ __('Base :baseFare · KM :kmFare · Hour :hourFare · Airport :airportSurcharge', [
                        'baseFare' => currency($global->base_fare),
                        'kmFare' => currency($global->km_fare),
                        'hourFare' => currency($global->hour_fare),
                        'airportSurcharge' => currency($global->airport_surcharge),
                    ]) }}
                </p>
                <p class="mt-1 text-xs text-luxury-muted">{{ __("Used by any fleet category without its own custom pricing rule.") }}</p>
            </div>

            @permission('pricing.edit')
                <a href="{{ route('admin.pricing.global.edit') }}">
                    <x-admin.button type="button" variant="primary">{{ __('Edit Global Pricing') }}</x-admin.button>
                </a>
            @endpermission
        </div>
    </div>

    {{-- Per-category rules --}}
    {{-- Desktop: table --}}
    <div class="hidden overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal sm:block">
        <div class="overflow-x-auto">
            <table class="w-full text-start text-sm">
                <thead>
                    <tr class="border-b border-luxury-border text-xs uppercase tracking-wider text-luxury-muted">
                        <th class="px-6 py-3 font-medium">{{ __('Category') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Base Fare') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('KM Fare') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Hour Fare') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Airport Surcharge') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Pricing Source') }}</th>
                        <th class="px-6 py-3 text-end font-medium">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-luxury-border/60">
                    @forelse ($categories as $category)
                        @php $rule = $category->pricingRule; @endphp
                        <tr class="hover:bg-luxury-graphite">
                            <td class="px-6 py-3 font-medium text-luxury-white">{{ $category->name }}</td>
                            <td class="px-6 py-3 text-luxury-muted">{{ currency($rule?->is_active ? $rule->base_fare : $global->base_fare) }}</td>
                            <td class="px-6 py-3 text-luxury-muted">{{ currency($rule?->is_active ? $rule->km_fare : $global->km_fare) }}</td>
                            <td class="px-6 py-3 text-luxury-muted">{{ currency($rule?->is_active ? $rule->hour_fare : $global->hour_fare) }}</td>
                            <td class="px-6 py-3 text-luxury-muted">{{ currency($rule?->is_active ? $rule->airport_surcharge : $global->airport_surcharge) }}</td>
                            <td class="px-6 py-3">
                                @if ($rule?->is_active)
                                    <span class="rounded-full bg-luxury-secondary/10 px-2.5 py-1 text-xs font-medium text-luxury-secondary">{{ __('Custom') }}</span>
                                @else
                                    <span class="rounded-full bg-luxury-slate px-2.5 py-1 text-xs font-medium text-luxury-muted">{{ __('Global Default') }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    @permission('pricing.edit')
                                        <a href="{{ route('admin.pricing.category.edit', $category) }}" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                            {{ $rule ? __('Edit') : __('Set Custom Pricing') }}
                                        </a>
                                    @endpermission
                                    @if ($rule)
                                        @permission('pricing.delete')
                                            <form method="POST" action="{{ route('admin.pricing.category.reset', $category) }}" onsubmit="return confirm('{{ __('Reset this category to the global default pricing?') }}');">
                                                @csrf
                                                <button type="submit" class="rounded-lg border border-red-500/30 px-3 py-1.5 text-xs font-medium text-red-400 transition hover:bg-red-500/10">
                                                    {{ __('Reset to Default') }}
                                                </button>
                                            </form>
                                        @endpermission
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-luxury-muted">{{ __('No fleet categories found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Mobile: cards --}}
    <div class="space-y-3 sm:hidden">
        @forelse ($categories as $category)
            @php $rule = $category->pricingRule; @endphp
            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-4">
                <div class="flex items-center justify-between gap-3">
                    <p class="truncate text-sm font-medium text-luxury-white">{{ $category->name }}</p>
                    @if ($rule?->is_active)
                        <span class="shrink-0 rounded-full bg-luxury-secondary/10 px-2.5 py-1 text-xs font-medium text-luxury-secondary">{{ __('Custom') }}</span>
                    @else
                        <span class="shrink-0 rounded-full bg-luxury-slate px-2.5 py-1 text-xs font-medium text-luxury-muted">{{ __('Global Default') }}</span>
                    @endif
                </div>

                <div class="mt-3 grid grid-cols-2 gap-3 border-t border-luxury-border pt-3 text-xs">
                    <div>
                        <p class="text-luxury-muted">{{ __('Base Fare') }}</p>
                        <p class="mt-0.5 font-medium text-luxury-white">{{ currency($rule?->is_active ? $rule->base_fare : $global->base_fare) }}</p>
                    </div>
                    <div>
                        <p class="text-luxury-muted">{{ __('KM Fare') }}</p>
                        <p class="mt-0.5 font-medium text-luxury-white">{{ currency($rule?->is_active ? $rule->km_fare : $global->km_fare) }}</p>
                    </div>
                    <div>
                        <p class="text-luxury-muted">{{ __('Hour Fare') }}</p>
                        <p class="mt-0.5 font-medium text-luxury-white">{{ currency($rule?->is_active ? $rule->hour_fare : $global->hour_fare) }}</p>
                    </div>
                    <div>
                        <p class="text-luxury-muted">{{ __('Airport Surcharge') }}</p>
                        <p class="mt-0.5 font-medium text-luxury-white">{{ currency($rule?->is_active ? $rule->airport_surcharge : $global->airport_surcharge) }}</p>
                    </div>
                </div>

                <div class="mt-3 flex flex-wrap items-center gap-2 border-t border-luxury-border pt-3">
                    @permission('pricing.edit')
                        <a href="{{ route('admin.pricing.category.edit', $category) }}" class="tap-scale rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                            {{ $rule ? __('Edit') : __('Set Custom Pricing') }}
                        </a>
                    @endpermission
                    @if ($rule)
                        @permission('pricing.delete')
                            <form method="POST" action="{{ route('admin.pricing.category.reset', $category) }}" onsubmit="return confirm('{{ __('Reset this category to the global default pricing?') }}');" class="ms-auto">
                                @csrf
                                <button type="submit" class="tap-scale rounded-lg border border-red-500/30 px-3 py-1.5 text-xs font-medium text-red-400 transition hover:bg-red-500/10">
                                    {{ __('Reset to Default') }}
                                </button>
                            </form>
                        @endpermission
                    @endif
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-10 text-center text-sm text-luxury-muted">
                {{ __('No fleet categories found.') }}
            </div>
        @endforelse
    </div>
</x-admin.layouts.app>
