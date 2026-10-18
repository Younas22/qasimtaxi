<x-admin.layouts.app :title="__('Drivers')">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Drivers') }}</h2>
            <p class="mt-1 text-sm text-luxury-muted">{{ __('Manage your chauffeurs — documents, vehicle assignment, and commission.') }}</p>
        </div>

        @permission('drivers.create')
            <a href="{{ route('admin.drivers.create') }}">
                <x-admin.button type="button" variant="primary">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    {{ __('Add Driver') }}
                </x-admin.button>
            </a>
        @endpermission
    </div>

    @if ($drivers->isEmpty())
        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal px-6 py-16 text-center text-luxury-muted">
            {{ __('No drivers added yet.') }}
        </div>
    @else
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($drivers as $driver)
                <div class="overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal transition hover:border-luxury-gold/40">
                    <div class="flex items-start gap-4 p-5">
                        <div class="relative shrink-0">
                            <div class="flex h-16 w-16 items-center justify-center overflow-hidden rounded-full border border-luxury-border bg-luxury-graphite">
                                @if ($driver->photo_url)
                                    <img src="{{ $driver->photo_url }}" alt="{{ $driver->name }}" class="h-full w-full object-cover">
                                @else
                                    <span class="text-lg font-semibold text-luxury-muted">{{ strtoupper(substr($driver->name, 0, 1)) }}</span>
                                @endif
                            </div>
                            <span class="absolute -end-0.5 -top-0.5 h-3.5 w-3.5 rounded-full border-2 border-luxury-charcoal {{ $driver->is_online ? 'bg-emerald-400' : 'bg-luxury-slate' }}" title="{{ $driver->is_online ? __('Online') : __('Offline') }}"></span>
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <p class="truncate font-semibold text-luxury-white">{{ $driver->name }}</p>
                                <x-admin.status-badge :active="$driver->status" />
                            </div>
                            <p class="truncate text-xs text-luxury-muted">{{ $driver->email }}</p>
                            <p class="text-xs text-luxury-muted">{{ $driver->phone ?? '—' }}</p>

                            <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-medium uppercase tracking-wide {{ $driver->is_online ? 'bg-emerald-500/10 text-emerald-400' : 'bg-luxury-slate text-luxury-muted' }}">
                                    {{ $driver->is_online ? __('Online') : __('Offline') }}
                                </span>
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-medium uppercase tracking-wide {{ $driver->is_available ? 'bg-luxury-gold/10 text-luxury-gold' : 'bg-luxury-slate text-luxury-muted' }}">
                                    {{ $driver->is_available ? __('Available') : __('Unavailable') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-luxury-border px-5 py-4">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-luxury-muted">{{ __('Assigned Vehicle') }}</span>
                            <span class="font-medium text-luxury-white">{{ $driver->vehicle?->name ?? __('Unassigned') }}</span>
                        </div>
                        <div class="mt-2 flex items-center justify-between text-xs">
                            <span class="text-luxury-muted">{{ __('Commission') }}</span>
                            <span class="font-medium text-luxury-gold">{{ number_format($driver->commission_rate, 2) }}%</span>
                        </div>
                        <div class="mt-2 flex items-center justify-between text-xs">
                            <span class="text-luxury-muted">{{ __('Rating') }}</span>
                            @if ($driver->average_rating)
                                <span class="flex items-center gap-1.5">
                                    <x-rating-stars :rating="$driver->average_rating" size="h-3 w-3" />
                                    <span class="font-medium text-luxury-white">{{ $driver->average_rating }}</span>
                                </span>
                            @else
                                <span class="text-luxury-muted">{{ __('No reviews yet') }}</span>
                            @endif
                        </div>
                    </div>

                    @permission('drivers.edit')
                        <div class="grid grid-cols-2 gap-2 border-t border-luxury-border p-3">
                            <form method="POST" action="{{ route('admin.drivers.toggle-online', $driver) }}">
                                @csrf
                                <button type="submit" class="w-full rounded-lg border border-luxury-border py-2 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                    {{ $driver->is_online ? __('Set Offline') : __('Set Online') }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.drivers.toggle-available', $driver) }}">
                                @csrf
                                <button type="submit" class="w-full rounded-lg border border-luxury-border py-2 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                    {{ $driver->is_available ? __('Set Unavailable') : __('Set Available') }}
                                </button>
                            </form>
                        </div>
                    @endpermission

                    <div class="flex items-center justify-end gap-2 border-t border-luxury-border p-3">
                        @permission('drivers.edit')
                            <a href="{{ route('admin.drivers.edit', $driver) }}" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                {{ __('Edit') }}
                            </a>
                            <form method="POST" action="{{ route('admin.drivers.toggle', $driver) }}">
                                @csrf
                                <button type="submit" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                    {{ $driver->status ? __('Disable') : __('Enable') }}
                                </button>
                            </form>
                        @endpermission
                        @permission('drivers.delete')
                            <form method="POST" action="{{ route('admin.drivers.destroy', $driver) }}" onsubmit="return confirm('{{ __('Delete this driver?') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-lg border border-red-500/30 px-3 py-1.5 text-xs font-medium text-red-400 transition hover:bg-red-500/10">
                                    {{ __('Delete') }}
                                </button>
                            </form>
                        @endpermission
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-admin.layouts.app>
