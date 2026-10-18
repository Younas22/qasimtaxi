@php
    $googleMapsKey = config('services.google_maps.key');
@endphp

<x-customer.layouts.app :title="__('Saved Addresses')">
    @once
        @if ($googleMapsKey)
            <script async src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsKey }}&libraries=places&callback=__initAddressMaps"></script>
            <script>
                window.__initAddressMaps = function () {
                    const attach = () => {
                        document.querySelectorAll('[data-address-form]').forEach((el) => {
                            Alpine.$data(el).initAutocomplete();
                        });
                    };

                    if (window.Alpine) {
                        attach();
                    } else {
                        document.addEventListener('alpine:initialized', attach);
                    }
                };
            </script>
        @endif
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icons@7.2.3/css/flag-icons.min.css">
    @endonce

    <div x-data="{ showForm: false }">
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Saved Addresses') }}</h2>
                <p class="mt-1 text-sm text-luxury-muted">{{ __('Manage your frequent pickup and drop-off locations.') }}</p>
            </div>
            <button type="button" @click="showForm = !showForm" class="tap-scale inline-flex items-center gap-2 rounded-lg bg-luxury-gold px-4 py-2.5 text-sm font-semibold text-luxury-black transition hover:bg-luxury-gold-light">
                <x-icon name="calendar" class="h-4 w-4" />
                {{ __('Add Address') }}
            </button>
        </div>

        <div x-show="showForm" x-cloak x-transition class="mb-6 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6"
            x-data="addressForm(@js($countries))" data-address-form x-init="if (window.google?.maps?.places) { initAutocomplete(); }">
            <form method="POST" action="{{ route('customer.addresses.store') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                @csrf

                {{-- Address (Google Places Autocomplete) + Current Location — its own full row --}}
                <div class="sm:col-span-2">
                    <div class="mb-1 flex items-center justify-between">
                        <x-admin.input-label for="address_line" value="Address" />
                        <button type="button" @click="useCurrentLocation()" :disabled="locating"
                            class="tap-scale flex items-center gap-1.5 text-xs font-medium text-luxury-gold hover:text-luxury-gold-light disabled:cursor-not-allowed disabled:opacity-50">
                            <svg x-show="locating" x-cloak class="h-3.5 w-3.5 animate-spin" viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <x-icon name="map-pin" class="h-3.5 w-3.5" x-show="!locating" />
                            <span x-text="locating ? '{{ __('Locating…') }}' : '{{ __('Use Current Location') }}'"></span>
                        </button>
                    </div>
                    <textarea id="address_line" name="address_line" rows="2" required x-ref="addressInput" x-model="addressLine"
                        @input="lat = null; lng = null; placeId = null; cityName = null"
                        class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">{{ old('address_line') }}</textarea>
                    <x-admin.input-error :messages="$errors->get('address_line')" />
                    <p x-show="locateError" x-cloak x-text="locateError" class="mt-1 text-xs text-red-400"></p>
                    <p x-show="cityName" x-cloak class="mt-1 text-xs text-luxury-muted">
                        {{ __('City detected') }}: <span class="text-luxury-gold" x-text="cityName"></span>
                    </p>

                    {{-- Pin preview — so the customer can see, not just read,
                         the exact spot this address will resolve to. --}}
                    <div x-show="lat && lng" x-cloak class="mt-3 space-y-1">
                        <div id="address-map-preview" class="h-40 w-full overflow-hidden rounded-xl border border-luxury-border bg-luxury-graphite"></div>
                        <p class="flex items-center gap-1 text-[11px] text-luxury-muted">
                            <x-icon name="map-pin" class="h-3 w-3 text-luxury-gold" />
                            {{ __('This is the location that will be saved.') }}
                        </p>
                    </div>

                    <input type="hidden" name="lat" :value="lat ?? ''">
                    <input type="hidden" name="lng" :value="lng ?? ''">
                    <input type="hidden" name="place_id" :value="placeId ?? ''">
                    <input type="hidden" name="city_name" :value="cityName ?? ''">
                </div>

                {{-- Country — auto-filled from the address above, still searchable if it needs a manual pick --}}
                <div class="min-w-0" x-data="{ open: false, search: '' }" @click.outside="open = false; search = ''">
                    <x-admin.input-label value="{{ __('Country') }}" />
                    <div class="relative min-w-0">
                        <button type="button" @click="open = !open; search = ''; $nextTick(() => $refs.countrySearchInput?.focus())"
                            aria-haspopup="listbox" :aria-expanded="open"
                            class="flex w-full min-w-0 items-center gap-2 rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-start text-sm text-luxury-white transition hover:border-luxury-gold/40 focus:outline-none focus-visible:ring-2 focus-visible:ring-luxury-gold/50">
                            <template x-if="selectedCountry()">
                                <span class="fi shrink-0" :class="'fi-' + selectedCountry().iso2"></span>
                            </template>
                            <span class="min-w-0 flex-1 truncate" x-text="selectedCountry() ? selectedCountry().name : '{{ __('Select country') }}'"></span>
                            <x-icon name="chevron-down" class="h-3.5 w-3.5 shrink-0 text-luxury-muted transition-transform" x-bind:class="{ 'rotate-180': open }" />
                        </button>

                        <div x-show="open" x-cloak x-transition
                            class="absolute start-0 end-0 top-full z-30 mt-2 w-full min-w-0 overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal shadow-2xl">
                            <div class="border-b border-luxury-border/60 p-2">
                                <input type="text" x-ref="countrySearchInput" x-model="search" placeholder="{{ __('Search country') }}"
                                    class="w-full rounded-lg border border-luxury-border bg-luxury-black/40 px-3 py-1.5 text-xs text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                            </div>
                            <div class="max-h-40 overflow-y-auto p-1.5">
                                <template x-for="item in filteredCountries(search)" :key="item.iso2">
                                    <button type="button" role="option" @click="country = item.name; open = false; search = ''"
                                        class="flex w-full min-w-0 items-center gap-2 rounded-lg px-2.5 py-1.5 text-start text-sm transition hover:bg-luxury-graphite"
                                        :class="country === item.name ? 'text-luxury-gold' : 'text-luxury-muted'">
                                        <span class="fi shrink-0" :class="'fi-' + item.iso2"></span>
                                        <span class="min-w-0 flex-1 truncate" x-text="item.name"></span>
                                    </button>
                                </template>
                                <p x-show="filteredCountries(search).length === 0" class="px-3 py-4 text-center text-xs text-luxury-muted">{{ __('No countries found.') }}</p>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="country" :value="country ?? ''">
                </div>

                <label class="flex items-center gap-2 text-sm text-luxury-muted">
                    <input type="checkbox" name="is_default" value="1" class="rounded border-luxury-border bg-luxury-charcoal text-luxury-gold focus:ring-luxury-gold">
                    {{ __('Set as default') }}
                </label>
                <div class="sm:col-span-2">
                    <x-admin.button type="submit" variant="primary">{{ __('Save Address') }}</x-admin.button>
                </div>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($addresses as $address)
            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-5">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-2">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-luxury-gold/10 text-luxury-gold">
                            <x-icon name="map-pin" class="h-4 w-4" />
                        </span>
                        <div>
                            <p class="text-sm font-medium text-luxury-white">{{ $address->label }}</p>
                            @if ($address->is_default)
                                <span class="text-[11px] font-medium text-luxury-gold">{{ __('Default') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <p class="mt-3 text-sm text-luxury-muted">{{ $address->address_line }}</p>
                @php $cityLabel = $address->city_name ?: $address->city?->name; @endphp
                @if ($cityLabel || $address->country)
                    <p class="mt-1 text-xs text-luxury-muted">{{ collect([$cityLabel, $address->country])->filter()->implode(', ') }}</p>
                @endif

                <div class="mt-4 flex items-center gap-2 border-t border-luxury-border pt-4">
                    @unless ($address->is_default)
                        <form method="POST" action="{{ route('customer.addresses.default', $address) }}">
                            @csrf
                            <button type="submit" class="text-xs font-medium text-luxury-gold hover:text-luxury-gold-light">{{ __('Set Default') }}</button>
                        </form>
                        <span class="text-luxury-border">|</span>
                    @endunless
                    <x-confirm-modal
                        action="{{ route('customer.addresses.destroy', $address) }}"
                        method="DELETE"
                        title="{{ __('Remove Address') }}"
                        message="{{ __('This saved address will be permanently removed.') }}"
                        confirm-label="{{ __('Remove') }}"
                        trigger-label="{{ __('Remove') }}" />
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-2xl border border-luxury-border bg-luxury-charcoal p-12 text-center">
                <x-icon name="map-pin" class="mx-auto h-10 w-10 text-luxury-muted" />
                <p class="mt-4 text-sm text-luxury-muted">{{ __("You haven't saved any addresses yet.") }}</p>
            </div>
        @endforelse
    </div>

    @push('scripts')
        <script>
            function addressForm(countries) {
                return {
                    countries,
                    addressLine: @js(old('address_line', '')),
                    lat: null,
                    lng: null,
                    placeId: null,
                    country: @js(old('country', '')),
                    cityName: @js(old('city_name', '')),
                    autocompleteReady: false,
                    locating: false,
                    locateError: null,
                    previewMap: null,
                    previewMarker: null,

                    renderPreviewMap() {
                        if (! window.google?.maps || this.lat === null || this.lng === null) return;

                        const position = { lat: this.lat, lng: this.lng };

                        this.$nextTick(() => {
                            const el = document.getElementById('address-map-preview');
                            if (! el) return;

                            if (! this.previewMap) {
                                this.previewMap = new google.maps.Map(el, {
                                    center: position,
                                    zoom: 15,
                                    disableDefaultUI: true,
                                    gestureHandling: 'cooperative',
                                    styles: [
                                        { elementType: 'geometry', stylers: [{ color: '#1a1c20' }] },
                                        { elementType: 'labels.text.stroke', stylers: [{ color: '#1a1c20' }] },
                                        { elementType: 'labels.text.fill', stylers: [{ color: '#8b8d87' }] },
                                        { featureType: 'poi', stylers: [{ visibility: 'off' }] },
                                        { featureType: 'road', elementType: 'geometry', stylers: [{ color: '#2c2e33' }] },
                                        { featureType: 'road', elementType: 'geometry.stroke', stylers: [{ color: '#1a1c20' }] },
                                        { featureType: 'transit', stylers: [{ visibility: 'off' }] },
                                        { featureType: 'water', elementType: 'geometry', stylers: [{ color: '#0e1013' }] },
                                    ],
                                });
                                this.previewMarker = new google.maps.Marker({ position, map: this.previewMap });
                            } else {
                                this.previewMap.setCenter(position);
                                this.previewMarker.setPosition(position);
                            }
                        });
                    },

                    selectedCountry() {
                        return this.countries.find((item) => item.name === this.country) || null;
                    },

                    filteredCountries(search) {
                        const term = (search || '').trim().toLowerCase();
                        if (! term) return this.countries;
                        return this.countries.filter((item) => item.name.toLowerCase().includes(term));
                    },

                    // Takes Google's returned locality/postal_town directly as
                    // the city name — no dropdown, no matching against any
                    // limited admin-curated list, just whatever the address
                    // actually says.
                    applyLocationComponents(components) {
                        const countryComponent = (components || []).find((c) => c.types.includes('country'));
                        if (countryComponent) {
                            const match = this.countries.find((item) => item.name.toLowerCase() === countryComponent.long_name.toLowerCase());
                            this.country = match ? match.name : countryComponent.long_name;
                        }

                        const cityComponent = (components || []).find((c) => c.types.includes('locality'))
                            || (components || []).find((c) => c.types.includes('postal_town'));
                        this.cityName = cityComponent ? cityComponent.long_name : null;
                    },

                    initAutocomplete() {
                        if (! window.google?.maps?.places || this.autocompleteReady) return;
                        this.autocompleteReady = true;

                        const el = this.$refs.addressInput;
                        if (! el) return;

                        const autocomplete = new google.maps.places.Autocomplete(el, { fields: ['place_id', 'geometry', 'formatted_address', 'address_components'] });
                        autocomplete.addListener('place_changed', () => {
                            const place = autocomplete.getPlace();
                            if (! place.geometry) return;

                            this.addressLine = place.formatted_address || this.addressLine;
                            this.lat = place.geometry.location.lat();
                            this.lng = place.geometry.location.lng();
                            this.placeId = place.place_id || null;

                            this.applyLocationComponents(place.address_components);
                            this.renderPreviewMap();
                        });
                    },

                    useCurrentLocation() {
                        this.locateError = null;

                        if (! navigator.geolocation) {
                            this.locateError = @js(__('Geolocation is not supported by your browser.'));
                            return;
                        }

                        this.locating = true;

                        navigator.geolocation.getCurrentPosition(
                            (position) => {
                                const { latitude, longitude } = position.coords;
                                this.lat = latitude;
                                this.lng = longitude;
                                this.renderPreviewMap();

                                if (! window.google?.maps) {
                                    this.locating = false;
                                    return;
                                }

                                new google.maps.Geocoder().geocode({ location: { lat: latitude, lng: longitude } }, (results, status) => {
                                    this.locating = false;

                                    if (status !== 'OK' || ! results?.length) {
                                        this.locateError = @js(__('Could not determine your address from this location.'));
                                        return;
                                    }

                                    const result = results[0];
                                    this.addressLine = result.formatted_address || this.addressLine;
                                    this.placeId = result.place_id || null;

                                    this.applyLocationComponents(result.address_components);
                                });
                            },
                            (error) => {
                                this.locating = false;
                                this.locateError = error.code === error.PERMISSION_DENIED
                                    ? @js(__('Location access was denied. Please allow location access and try again.'))
                                    : @js(__('Could not get your current location. Please try again.'));
                            },
                            { enableHighAccuracy: true, timeout: 10000 }
                        );
                    },
                };
            }
        </script>
    @endpush
</x-customer.layouts.app>
