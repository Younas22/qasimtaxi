<x-admin.layouts.app :title="__('Popular Routes')">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Popular Routes') }}</h2>
            <p class="mt-1 text-sm text-luxury-muted">{{ __('Airport, city, and intercity routes shown on the public website.') }}</p>
        </div>

        <div class="flex items-center gap-2">
            @permission('routes.view')
                <a href="{{ route('admin.popular-routes.route-types.index') }}" class="rounded-lg border border-luxury-border px-4 py-2.5 text-sm font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                    {{ __('Route Types') }}
                </a>
            @endpermission

            @permission('routes.create')
                <a href="{{ route('admin.popular-routes.create') }}">
                    <x-admin.button type="button" variant="primary">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        {{ __('Add Route') }}
                    </x-admin.button>
                </a>
            @endpermission
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" class="mb-6 flex flex-wrap items-center gap-3">
        <select name="route_type_id" onchange="this.form.submit()"
            class="rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
            <option value="">{{ __('All Types') }}</option>
            @foreach ($routeTypes as $routeType)
                <option value="{{ $routeType->id }}" @selected((string) request('route_type_id') === (string) $routeType->id)>{{ $routeType->name }}</option>
            @endforeach
        </select>

        @if (request()->filled('route_type_id'))
            <a href="{{ route('admin.popular-routes.index') }}" class="text-xs text-luxury-muted hover:text-luxury-gold">{{ __('Clear filter') }}</a>
        @endif
    </form>

    @if ($routes->isEmpty())
        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal px-6 py-10 text-center text-luxury-muted">
            {{ __('No popular routes added yet.') }}
        </div>
    @else
        {{-- Desktop / tablet: full table. Hidden below md — a 7-column
             table has no comfortable way to shrink onto a phone width, so
             mobile gets the card list below instead of a squeezed or
             sideways-scrolling table. --}}
        <div class="hidden overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal md:block">
            <div class="overflow-x-auto">
                <table class="w-full text-start text-sm">
                    <thead>
                        <tr class="border-b border-luxury-border text-xs uppercase tracking-wider text-luxury-muted">
                            <th class="px-6 py-3 font-medium">{{ __('Photo') }}</th>
                            <th class="px-6 py-3 font-medium">{{ __('Type') }}</th>
                            <th class="px-6 py-3 font-medium">{{ __('Pickup') }}</th>
                            <th class="px-6 py-3 font-medium">{{ __('Dropoff') }}</th>
                            <th class="px-6 py-3 font-medium">{{ __('Distance') }}</th>
                            <th class="px-6 py-3 font-medium">{{ __('Price') }}</th>
                            <th class="px-6 py-3 font-medium">{{ __('Status') }}</th>
                            <th class="px-6 py-3 text-end font-medium">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-luxury-border/60">
                        @foreach ($routes as $route)
                            <tr class="hover:bg-luxury-graphite">
                                <td class="px-6 py-3">
                                    @if ($route->image_url)
                                        <img src="{{ $route->image_url }}" alt="" class="h-10 w-16 rounded-lg border border-luxury-border object-cover">
                                    @else
                                        <span class="flex h-10 w-16 items-center justify-center rounded-lg border border-dashed border-luxury-border text-luxury-muted">
                                            <x-icon name="map-pin" class="h-4 w-4" />
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-3">
                                    <span class="rounded-full bg-luxury-gold/10 px-2.5 py-1 text-xs font-medium text-luxury-gold">{{ $route->routeType?->name ?? '—' }}</span>
                                </td>
                                <td class="px-6 py-3 font-medium text-luxury-white">{{ $route->pickup }}</td>
                                <td class="px-6 py-3 text-luxury-muted">{{ $route->dropoff }}</td>
                                <td class="px-6 py-3 text-luxury-muted">{{ $route->distance ? $route->distance.' '.$route->distance_unit : '—' }}</td>
                                <td class="px-6 py-3">
                                    @if ($route->has_discount)
                                        <span class="text-luxury-muted line-through">{{ currency($route->original_price) }}</span>
                                        <span class="ms-1 font-medium text-luxury-white">{{ currency($route->estimated_price) }}</span>
                                    @else
                                        <span class="text-luxury-muted">{{ $route->estimated_price ? currency($route->estimated_price) : '—' }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3"><x-admin.status-badge :active="$route->is_active" /></td>
                                <td class="px-6 py-3">
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        @permission('routes.edit')
                                            <a href="{{ route('admin.popular-routes.edit', $route) }}" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                                {{ __('Edit') }}
                                            </a>
                                            <form method="POST" action="{{ route('admin.popular-routes.toggle', $route) }}">
                                                @csrf
                                                <button type="submit" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                                    {{ $route->is_active ? __('Disable') : __('Enable') }}
                                                </button>
                                            </form>
                                        @endpermission

                                        @permission('routes.delete')
                                            <form method="POST" action="{{ route('admin.popular-routes.destroy', $route) }}" onsubmit="return confirm('{{ __('Delete this route?') }}');">
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
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Mobile: one card per route. --}}
        <div class="space-y-3 md:hidden">
            @foreach ($routes as $route)
                <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-4">
                    <div class="flex items-start justify-between gap-3">
                        <span class="rounded-full bg-luxury-gold/10 px-2.5 py-1 text-xs font-medium text-luxury-gold">{{ $route->routeType?->name ?? '—' }}</span>
                        <x-admin.status-badge :active="$route->is_active" />
                    </div>

                    @if ($route->image_url)
                        <img src="{{ $route->image_url }}" alt="" class="mt-3 h-28 w-full rounded-xl border border-luxury-border object-cover">
                    @endif

                    <div class="mt-3 flex items-start gap-3">
                        <div class="mt-1 flex flex-col items-center">
                            <span class="h-2 w-2 shrink-0 rounded-full bg-luxury-gold"></span>
                            <span class="my-1 h-5 w-px bg-luxury-border"></span>
                            <x-icon name="map-pin" class="h-3.5 w-3.5 shrink-0 text-luxury-gold" />
                        </div>
                        <div class="min-w-0 flex-1 space-y-2">
                            <p class="truncate text-sm font-medium text-luxury-white">{{ $route->pickup }}</p>
                            <p class="truncate text-sm font-medium text-luxury-white">{{ $route->dropoff }}</p>
                        </div>
                    </div>

                    <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-luxury-muted">
                        <span>{{ $route->distance ? $route->distance.' '.$route->distance_unit : '—' }}</span>
                        @if ($route->has_discount)
                            <span>
                                <span class="line-through">{{ currency($route->original_price) }}</span>
                                <span class="ms-1 font-medium text-luxury-white">{{ currency($route->estimated_price) }}</span>
                            </span>
                        @else
                            <span>{{ $route->estimated_price ? currency($route->estimated_price) : '—' }}</span>
                        @endif
                    </div>

                    <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-luxury-border pt-3">
                        @permission('routes.edit')
                            <a href="{{ route('admin.popular-routes.edit', $route) }}" class="flex-1 rounded-lg border border-luxury-border px-3 py-2 text-center text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                {{ __('Edit') }}
                            </a>
                            <form method="POST" action="{{ route('admin.popular-routes.toggle', $route) }}" class="flex-1">
                                @csrf
                                <button type="submit" class="w-full rounded-lg border border-luxury-border px-3 py-2 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                    {{ $route->is_active ? __('Disable') : __('Enable') }}
                                </button>
                            </form>
                        @endpermission

                        @permission('routes.delete')
                            <form method="POST" action="{{ route('admin.popular-routes.destroy', $route) }}" onsubmit="return confirm('{{ __('Delete this route?') }}');" class="flex-1">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full rounded-lg border border-red-500/30 px-3 py-2 text-xs font-medium text-red-400 transition hover:bg-red-500/10">
                                    {{ __('Delete') }}
                                </button>
                            </form>
                        @endpermission
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if ($routes->hasPages())
        <div class="mt-6 flex items-center justify-between text-sm text-luxury-muted">
            <div>
                @if ($routes->onFirstPage())
                    <span class="cursor-not-allowed rounded-lg border border-luxury-border px-3 py-1.5 opacity-40">{{ __('Previous') }}</span>
                @else
                    <a href="{{ $routes->previousPageUrl() }}" class="rounded-lg border border-luxury-border px-3 py-1.5 hover:border-luxury-gold/40 hover:text-luxury-gold">{{ __('Previous') }}</a>
                @endif
            </div>
            <p>{{ __('Page :current of :last', ['current' => $routes->currentPage(), 'last' => $routes->lastPage()]) }}</p>
            <div>
                @if ($routes->hasMorePages())
                    <a href="{{ $routes->nextPageUrl() }}" class="rounded-lg border border-luxury-border px-3 py-1.5 hover:border-luxury-gold/40 hover:text-luxury-gold">{{ __('Next') }}</a>
                @else
                    <span class="cursor-not-allowed rounded-lg border border-luxury-border px-3 py-1.5 opacity-40">{{ __('Next') }}</span>
                @endif
            </div>
        </div>
    @endif
</x-admin.layouts.app>
