<x-admin.layouts.app :title="__('Live Fleet')">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Live Fleet') }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ __('Every driver, their online status, current job and availability.') }}</p>
    </div>

    <div x-data="adminFleet(@js($rows), @js($office), @js(route('admin.fleet.data')))" x-init="init()" class="space-y-6">
        <x-dispatch-map id="admin-fleet-map" class="h-64 sm:h-80 lg:h-96" />

        <div class="flex items-center gap-3">
            <label for="fleet-category-filter" class="text-xs font-medium text-luxury-muted">{{ __('Vehicle Type') }}</label>
            <select id="fleet-category-filter" x-model="categoryFilter" @change="renderMap()"
                class="rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                <option value="">{{ __('All Vehicle Types') }}</option>
                <template x-for="category in categories()" :key="category">
                    <option :value="category" x-text="category"></option>
                </template>
            </select>
        </div>

        {{-- Desktop: full table --}}
        <div class="hidden overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal sm:block">
            <div class="overflow-x-auto">
                <table class="w-full text-start text-sm">
                    <thead>
                        <tr class="border-b border-luxury-border text-xs uppercase tracking-wider text-luxury-muted">
                            <th class="px-6 py-3 font-medium">{{ __('Driver') }}</th>
                            <th class="px-6 py-3 font-medium">{{ __('Vehicle') }}</th>
                            <th class="px-6 py-3 font-medium">{{ __('Status') }}</th>
                            <th class="px-6 py-3 font-medium">{{ __('Location') }}</th>
                            <th class="px-6 py-3 font-medium">{{ __('Current Ride') }}</th>
                            <th class="px-6 py-3 font-medium">{{ __('Ride Ends') }}</th>
                            <th class="px-6 py-3 font-medium">{{ __('Distance from Office') }}</th>
                            <th class="px-6 py-3 font-medium">{{ __('Next Pickup') }}</th>
                            <th class="px-6 py-3 font-medium">{{ __('ETA') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-luxury-border/60">
                        <template x-for="row in filteredRows()" :key="row.id">
                            <tr class="hover:bg-luxury-graphite" :class="!row.online ? 'opacity-50' : ''">
                                <td class="px-6 py-3 font-medium text-luxury-white" x-text="row.name"></td>
                                <td class="px-6 py-3 text-luxury-muted" x-text="row.vehicle_category || '—'"></td>
                                <td class="px-6 py-3">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-medium"
                                        :class="{
                                            'bg-blue-500/10 text-blue-400': row.status === 'busy',
                                            'bg-emerald-500/10 text-emerald-400': row.status === 'available',
                                            'bg-luxury-slate/40 text-luxury-muted': row.status === 'offline',
                                        }"
                                        x-text="row.status === 'busy' ? '{{ __('On Trip') }}' : (row.status === 'available' ? '{{ __('Online — Available') }}' : '{{ __('Offline') }}')"></span>
                                </td>
                                <td class="px-6 py-3 text-luxury-muted" x-text="row.location"></td>
                                <td class="px-6 py-3 text-luxury-muted" x-text="row.current_ride || '—'"></td>
                                <td class="px-6 py-3 text-luxury-muted" x-text="row.ride_ends_in_minutes !== null ? row.ride_ends_in_minutes + ' {{ __('min') }}' : '—'"></td>
                                <td class="px-6 py-3 text-luxury-muted" x-text="row.distance_from_office_km !== null ? row.distance_from_office_km + ' km' : '—'"></td>
                                <td class="px-6 py-3 text-luxury-muted" x-text="row.next_pickup || '—'"></td>
                                <td class="px-6 py-3 text-luxury-gold" x-text="row.eta_minutes !== null ? row.eta_minutes + ' {{ __('min') }}' : '—'"></td>
                            </tr>
                        </template>
                        <tr x-show="filteredRows().length === 0">
                            <td colspan="9" class="px-6 py-10 text-center text-luxury-muted">{{ __('No drivers match this filter.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Mobile: cards, same reactive data as the table above. --}}
        <div class="space-y-3 sm:hidden">
            <template x-for="row in filteredRows()" :key="row.id">
                <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-4" :class="!row.online ? 'opacity-50' : ''">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-luxury-white" x-text="row.name"></p>
                            <p class="truncate text-xs text-luxury-muted" x-text="row.vehicle_category || '—'"></p>
                        </div>
                        <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-medium"
                            :class="{
                                'bg-blue-500/10 text-blue-400': row.status === 'busy',
                                'bg-emerald-500/10 text-emerald-400': row.status === 'available',
                                'bg-luxury-slate/40 text-luxury-muted': row.status === 'offline',
                            }"
                            x-text="row.status === 'busy' ? '{{ __('On Trip') }}' : (row.status === 'available' ? '{{ __('Online — Available') }}' : '{{ __('Offline') }}')"></span>
                    </div>

                    <div class="mt-3 flex items-start gap-2 border-t border-luxury-border pt-3">
                        <x-icon name="map-pin" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-luxury-gold" />
                        <p class="min-w-0 flex-1 truncate text-xs text-luxury-muted" x-text="row.location"></p>
                    </div>

                    <div class="mt-3 grid grid-cols-2 gap-3 border-t border-luxury-border pt-3 text-xs">
                        <div>
                            <p class="text-luxury-muted">{{ __('Current Ride') }}</p>
                            <p class="mt-0.5 font-medium text-luxury-white" x-text="row.current_ride || '—'"></p>
                        </div>
                        <div>
                            <p class="text-luxury-muted">{{ __('Ride Ends') }}</p>
                            <p class="mt-0.5 font-medium text-luxury-white" x-text="row.ride_ends_in_minutes !== null ? row.ride_ends_in_minutes + ' {{ __('min') }}' : '—'"></p>
                        </div>
                        <div>
                            <p class="text-luxury-muted">{{ __('Next Pickup') }}</p>
                            <p class="mt-0.5 font-medium text-luxury-white" x-text="row.next_pickup || '—'"></p>
                        </div>
                        <div>
                            <p class="text-luxury-muted">{{ __('ETA') }}</p>
                            <p class="mt-0.5 font-medium text-luxury-gold" x-text="row.eta_minutes !== null ? row.eta_minutes + ' {{ __('min') }}' : '—'"></p>
                        </div>
                    </div>
                </div>
            </template>

            <div x-show="filteredRows().length === 0" class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-10 text-center text-sm text-luxury-muted">
                {{ __('No drivers match this filter.') }}
            </div>
        </div>
    </div>

    <script>
        function adminFleet(initialRows, office, dataUrl) {
            return {
                rows: initialRows,
                office,
                dataUrl,
                categoryFilter: '',

                init() {
                    this.renderMap();
                    setInterval(() => this.poll(), 20000);
                },

                categories() {
                    return [...new Set(this.rows.map((row) => row.vehicle_category).filter(Boolean))].sort();
                },

                filteredRows() {
                    if (! this.categoryFilter) return this.rows;
                    return this.rows.filter((row) => row.vehicle_category === this.categoryFilter);
                },

                renderMap() {
                    const visible = this.filteredRows();

                    window.renderDispatchMap('admin-fleet-map', {
                        office: this.office,
                        driver: null,
                        pickup: this.office || (visible[0] ? { lat: visible[0].lat, lng: visible[0].lng } : null),
                        showRoute: false,
                        routeFrom: null,
                    });

                    visible.forEach((row) => {
                        if (row.lat && row.lng && window.google) {
                            new google.maps.Marker({
                                position: { lat: row.lat, lng: row.lng },
                                map: window.__dispatchMapInstances['admin-fleet-map']?.map,
                                icon: {
                                    path: google.maps.SymbolPath.CIRCLE,
                                    scale: 7,
                                    fillColor: row.status === 'busy' ? '#f97316' : '#3b82f6',
                                    fillOpacity: 1,
                                    strokeColor: '#ffffff',
                                    strokeWeight: 2,
                                },
                                title: row.name,
                            });
                        }
                    });
                },

                async poll() {
                    try {
                        const response = await fetch(this.dataUrl, { headers: { Accept: 'application/json' } });
                        if (! response.ok) return;
                        const json = await response.json();
                        this.rows = json.rows;
                        this.office = json.office;
                        this.renderMap();
                    } catch (e) {
                        // Next tick will retry.
                    }
                },
            };
        }
    </script>
</x-admin.layouts.app>
