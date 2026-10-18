<x-admin.layouts.app :title="__('Vehicles')">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Vehicles') }}</h2>
            <p class="mt-1 text-sm text-luxury-muted">{{ __('Manage your fleet — pricing, features, and photos.') }}</p>
        </div>

        <div class="flex items-center gap-2">
            @permission('vehicles.view')
                <a href="{{ route('admin.vehicles.categories.index') }}" class="rounded-lg border border-luxury-border px-4 py-2.5 text-sm font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                    {{ __('Categories') }}
                </a>
            @endpermission

            @permission('vehicles.create')
                <a href="{{ route('admin.vehicles.create') }}">
                    <x-admin.button type="button" variant="primary">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        {{ __('Add Vehicle') }}
                    </x-admin.button>
                </a>
            @endpermission
        </div>
    </div>

    @if ($vehicles->isEmpty())
        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal px-6 py-16 text-center text-luxury-muted">
            {{ __('No vehicles added yet.') }}
        </div>
    @else
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($vehicles as $vehicle)
                <div class="overflow-hidden rounded-2xl border {{ $vehicle->status ? 'border-luxury-border' : 'border-red-500/30' }} bg-luxury-charcoal transition hover:border-luxury-gold/40">
                    <div class="relative aspect-[16/10] w-full overflow-hidden bg-luxury-graphite">
                        @if ($vehicle->image_url)
                            <img src="{{ $vehicle->image_url }}" alt="{{ $vehicle->name }}" class="h-full w-full object-cover {{ $vehicle->status ? '' : 'opacity-50 grayscale' }}">
                        @else
                            <div class="flex h-full w-full items-center justify-center">
                                <svg class="h-10 w-10 text-luxury-muted" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 17a2 2 0 11-4 0 2 2 0 014 0zM20 17a2 2 0 11-4 0 2 2 0 014 0zM6 17H4v-4l1.5-4.5A2 2 0 017.4 7h9.2a2 2 0 011.9 1.5L20 13v4h-2m-12 0h8m-8 0H4" />
                                </svg>
                            </div>
                        @endif

                        <div class="absolute start-3 top-3 flex items-center gap-2">
                            @if ($vehicle->category)
                                <span class="rounded-full bg-luxury-black/70 px-2.5 py-1 text-[11px] font-medium uppercase tracking-wide text-luxury-gold backdrop-blur">
                                    {{ $vehicle->category->name }}
                                </span>
                            @endif
                        </div>

                        <div class="absolute end-3 top-3">
                            <x-admin.status-badge :active="$vehicle->status" class="backdrop-blur" />
                        </div>
                    </div>

                    <div class="p-5">
                        <div class="flex items-center justify-between gap-2">
                            <p class="font-semibold text-luxury-white">{{ $vehicle->name }}</p>
                            @if ($vehicle->average_rating)
                                <span class="flex shrink-0 items-center gap-1 text-xs">
                                    <x-rating-stars :rating="$vehicle->average_rating" size="h-3 w-3" />
                                    <span class="font-medium text-luxury-white">{{ $vehicle->average_rating }}</span>
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-luxury-muted">{{ $vehicle->brand }} {{ $vehicle->model }} &middot; {{ $vehicle->year }}</p>

                        <div class="mt-4 grid grid-cols-3 gap-2 text-center text-xs text-luxury-muted">
                            <div class="rounded-lg bg-luxury-graphite py-2">
                                <p class="font-semibold text-luxury-white">{{ $vehicle->seats }}</p>
                                <p>{{ __('Seats') }}</p>
                            </div>
                            <div class="rounded-lg bg-luxury-graphite py-2">
                                <p class="font-semibold text-luxury-white">{{ $vehicle->luggage }}</p>
                                <p>{{ __('Luggage') }}</p>
                            </div>
                            <div class="rounded-lg bg-luxury-graphite py-2">
                                <p class="font-semibold capitalize text-luxury-white">{{ $vehicle->transmission }}</p>
                                <p>{{ __('Gear') }}</p>
                            </div>
                        </div>

                        <div class="mt-4 flex items-center justify-between border-t border-luxury-border pt-4">
                            <div>
                                <p class="text-[11px] uppercase tracking-wide text-luxury-muted">{{ __('Base Fare') }}</p>
                                <p class="text-lg font-semibold text-luxury-gold">{{ currency($vehicle->base_fare) }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                @permission('vehicles.edit')
                                    <a href="{{ route('admin.vehicles.edit', $vehicle) }}" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                        {{ __('Edit') }}
                                    </a>
                                @endpermission
                                @permission('vehicles.delete')
                                    <form method="POST" action="{{ route('admin.vehicles.destroy', $vehicle) }}" onsubmit="return confirm('{{ __('Delete this vehicle?') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg border border-red-500/30 px-3 py-1.5 text-xs font-medium text-red-400 transition hover:bg-red-500/10">
                                            {{ __('Delete') }}
                                        </button>
                                    </form>
                                @endpermission
                            </div>
                        </div>

                        @permission('vehicles.edit')
                            <form method="POST" action="{{ route('admin.vehicles.toggle', $vehicle) }}" class="mt-3">
                                @csrf
                                @if ($vehicle->status)
                                    <button type="submit" class="w-full rounded-lg border border-red-500/30 py-2 text-xs font-medium text-red-400 transition hover:bg-red-500/10">
                                        {{ __('Disable Vehicle') }}
                                    </button>
                                @else
                                    <button type="submit" class="w-full rounded-lg border border-emerald-500/30 py-2 text-xs font-medium text-emerald-400 transition hover:bg-emerald-500/10">
                                        {{ __('Enable Vehicle') }}
                                    </button>
                                @endif
                            </form>
                        @endpermission
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-admin.layouts.app>
