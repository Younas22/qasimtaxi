@php $vehicle = $vehicle ?? null; @endphp

<div class="space-y-6">
    {{-- Basic Information --}}
    <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
        <h3 class="text-sm font-semibold text-luxury-white">{{ __('Basic Information') }}</h3>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <x-admin.input-label for="vehicle_category_id" value="{{ __('Category') }}" />
                <select id="vehicle_category_id" name="vehicle_category_id" required
                    class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
                    <option value="">{{ __('Select a category') }}</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('vehicle_category_id', $vehicle?->vehicle_category_id) == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                <x-admin.input-error :messages="$errors->get('vehicle_category_id')" />
            </div>

            <div>
                <x-admin.input-label for="name" value="{{ __('Vehicle Name') }}" />
                <x-admin.text-input id="name" name="name" type="text" value="{{ old('name', $vehicle?->name) }}" placeholder="{{ __('e.g. Mercedes-Benz E-Class') }}" required autofocus />
                <x-admin.input-error :messages="$errors->get('name')" />
            </div>

            <div>
                <x-admin.input-label for="plate_number" value="{{ __('Plate Number') }}" />
                <x-admin.text-input id="plate_number" name="plate_number" type="text" value="{{ old('plate_number', $vehicle?->plate_number) }}" placeholder="{{ __('Optional') }}" />
                <x-admin.input-error :messages="$errors->get('plate_number')" />
            </div>

            <div>
                <x-admin.input-label for="brand" value="{{ __('Brand') }}" />
                <x-admin.text-input id="brand" name="brand" type="text" value="{{ old('brand', $vehicle?->brand) }}" placeholder="{{ __('e.g. Mercedes-Benz') }}" required />
                <x-admin.input-error :messages="$errors->get('brand')" />
            </div>

            <div>
                <x-admin.input-label for="model" value="{{ __('Model') }}" />
                <x-admin.text-input id="model" name="model" type="text" value="{{ old('model', $vehicle?->model) }}" placeholder="{{ __('e.g. E-Class') }}" required />
                <x-admin.input-error :messages="$errors->get('model')" />
            </div>

            <div>
                <x-admin.input-label for="year" value="{{ __('Year') }}" />
                <x-admin.text-input id="year" name="year" type="number" min="1990" :max="now()->year + 1" value="{{ old('year', $vehicle?->year) }}" required />
                <x-admin.input-error :messages="$errors->get('year')" />
            </div>

            <div>
                <x-admin.input-label for="seats" value="{{ __('Seats') }}" />
                <x-admin.text-input id="seats" name="seats" type="number" min="1" max="60" value="{{ old('seats', $vehicle?->seats ?? 4) }}" required />
                <x-admin.input-error :messages="$errors->get('seats')" />
            </div>

            <div>
                <x-admin.input-label for="luggage" value="{{ __('Luggage') }}" />
                <x-admin.text-input id="luggage" name="luggage" type="number" min="0" max="60" value="{{ old('luggage', $vehicle?->luggage ?? 2) }}" required />
                <x-admin.input-error :messages="$errors->get('luggage')" />
            </div>

            <div>
                <x-admin.input-label for="transmission" value="{{ __('Transmission') }}" />
                <select id="transmission" name="transmission" required
                    class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
                    <option value="automatic" @selected(old('transmission', $vehicle?->transmission ?? 'automatic') === 'automatic')>{{ __('Automatic') }}</option>
                    <option value="manual" @selected(old('transmission', $vehicle?->transmission) === 'manual')>{{ __('Manual') }}</option>
                </select>
                <x-admin.input-error :messages="$errors->get('transmission')" />
            </div>

            <div>
                <x-admin.input-label for="fuel_type" value="{{ __('Fuel Type') }}" />
                <select id="fuel_type" name="fuel_type" required
                    class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
                    @foreach (['petrol' => 'Petrol', 'diesel' => 'Diesel', 'hybrid' => 'Hybrid', 'electric' => 'Electric'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('fuel_type', $vehicle?->fuel_type ?? 'petrol') === $value)>{{ __($label) }}</option>
                    @endforeach
                </select>
                <x-admin.input-error :messages="$errors->get('fuel_type')" />
            </div>

            <div class="sm:col-span-2">
                <x-admin.input-label for="description" value="{{ __('Description') }}" />
                <textarea id="description" name="description" rows="3" placeholder="{{ __('Optional description shown to customers') }}"
                    class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">{{ old('description', $vehicle?->description) }}</textarea>
                <x-admin.input-error :messages="$errors->get('description')" />
            </div>
        </div>
    </div>

    {{-- Pricing --}}
    <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
        <div>
            <h3 class="text-sm font-semibold text-luxury-white">{{ __('Pricing') }}</h3>
            <p class="text-xs text-luxury-muted">{{ __('Stored in the default currency (:code) and converted automatically for display.', ['code' => \App\Models\Currency::default()?->code ?? 'USD']) }}</p>
        </div>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <div>
                <x-admin.input-label for="base_fare" value="{{ __('Base Fare') }}" />
                <x-admin.text-input id="base_fare" name="base_fare" type="number" step="0.01" min="0" value="{{ old('base_fare', $vehicle?->base_fare ?? 0) }}" required />
                <x-admin.input-error :messages="$errors->get('base_fare')" />
            </div>

            <div>
                <x-admin.input-label for="price_per_km" value="{{ __('Price per KM') }}" />
                <x-admin.text-input id="price_per_km" name="price_per_km" type="number" step="0.01" min="0" value="{{ old('price_per_km', $vehicle?->price_per_km ?? 0) }}" required />
                <x-admin.input-error :messages="$errors->get('price_per_km')" />
            </div>

            <div>
                <x-admin.input-label for="price_per_hour" value="{{ __('Price per Hour') }}" />
                <x-admin.text-input id="price_per_hour" name="price_per_hour" type="number" step="0.01" min="0" value="{{ old('price_per_hour', $vehicle?->price_per_hour ?? 0) }}" required />
                <x-admin.input-error :messages="$errors->get('price_per_hour')" />
            </div>

            <div>
                <x-admin.input-label for="airport_price" value="{{ __('Airport Price') }}" />
                <x-admin.text-input id="airport_price" name="airport_price" type="number" step="0.01" min="0" value="{{ old('airport_price', $vehicle?->airport_price ?? 0) }}" required />
                <x-admin.input-error :messages="$errors->get('airport_price')" />
            </div>

            <div>
                <x-admin.input-label for="night_charges" value="{{ __('Night Charges') }}" />
                <x-admin.text-input id="night_charges" name="night_charges" type="number" step="0.01" min="0" value="{{ old('night_charges', $vehicle?->night_charges ?? 0) }}" required />
                <x-admin.input-error :messages="$errors->get('night_charges')" />
            </div>
        </div>
    </div>

    {{-- Features --}}
    <div class="space-y-4 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
        <h3 class="text-sm font-semibold text-luxury-white">{{ __('Features') }}</h3>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
            @php
                $features = [
                    'has_wifi' => ['label' => 'WiFi', 'icon' => 'M8.288 15.038a5.25 5.25 0 017.424 0M5.106 11.856c3.807-3.808 9.98-3.808 13.788 0M1.923 8.673c5.62-5.619 14.532-5.619 20.154 0M12 18.75h.008v.008H12v-.008z'],
                    'has_water' => ['label' => 'Water', 'icon' => 'M12 3.75c-3.75 4.5-6 8.25-6 11.25a6 6 0 1012 0c0-3-2.25-6.75-6-11.25z'],
                    'has_charger' => ['label' => 'Charger', 'icon' => 'M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z'],
                    'has_baby_seat' => ['label' => 'Baby Seat', 'icon' => 'M9.75 9.75a3 3 0 116 0 3 3 0 01-6 0zM5.25 20.25a6.75 6.75 0 0113.5 0'],
                    'has_ac' => ['label' => 'AC', 'icon' => 'M12 3v18M3 8.25l9 4.5 9-4.5M3 15.75l9-4.5 9 4.5'],
                ];
            @endphp

            @foreach ($features as $key => $feature)
                <label class="flex cursor-pointer flex-col items-center gap-2 rounded-lg border px-3 py-4 text-center transition has-[:checked]:border-luxury-gold has-[:checked]:bg-luxury-gold/10 has-[:checked]:text-luxury-gold {{ old($key, $vehicle?->{$key} ?? ($key === 'has_ac')) ? 'border-luxury-gold bg-luxury-gold/10 text-luxury-gold' : 'border-luxury-border text-luxury-muted' }}">
                    <input type="checkbox" name="{{ $key }}" value="1" class="hidden" @checked(old($key, $vehicle?->{$key} ?? ($key === 'has_ac')))>
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $feature['icon'] }}" />
                    </svg>
                    <span class="text-xs font-medium">{{ __($feature['label']) }}</span>
                </label>
            @endforeach
        </div>
    </div>

    {{-- Images --}}
    <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
        <h3 class="text-sm font-semibold text-luxury-white">{{ __('Images') }}</h3>

        <div x-data="{ preview: '{{ $vehicle?->image_url }}' }">
            <x-admin.input-label value="{{ __('Featured Image') }}" />
            <div class="flex items-center gap-4">
                <div class="flex h-20 w-32 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-luxury-border bg-luxury-graphite">
                    <template x-if="preview">
                        <img :src="preview" alt="{{ __('Featured image preview') }}" class="h-full w-full object-cover">
                    </template>
                    <template x-if="!preview">
                        <span class="text-xs text-luxury-muted">{{ __('No image') }}</span>
                    </template>
                </div>
                <label class="flex-1 cursor-pointer rounded-lg border border-dashed border-luxury-border px-4 py-3 text-center text-xs text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                    <span>{{ __('Click to upload featured image') }}</span>
                    <input type="file" name="image" accept="image/*" class="hidden"
                        @change="preview = $event.target.files.length ? URL.createObjectURL($event.target.files[0]) : preview">
                </label>
            </div>
            <p class="mt-2 text-xs text-luxury-muted">{{ __('Shown on the vehicle card. Max 2MB.') }}</p>
            <x-admin.input-error :messages="$errors->get('image')" />
        </div>

        @if ($vehicle && $vehicle->images->isNotEmpty())
            <div>
                <x-admin.input-label value="{{ __('Gallery') }}" />
                <div class="grid grid-cols-3 gap-3 sm:grid-cols-5">
                    @foreach ($vehicle->images as $galleryImage)
                        <div class="group relative aspect-square overflow-hidden rounded-lg border border-luxury-border bg-luxury-graphite">
                            <img src="{{ $galleryImage->image_url }}" alt="{{ __('Gallery image') }}" class="h-full w-full object-cover">
                            <form method="POST" action="{{ route('admin.vehicles.images.destroy', $galleryImage) }}" onsubmit="return confirm('{{ __('Remove this image?') }}');" class="absolute end-1 top-1">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="flex h-6 w-6 items-center justify-center rounded-full bg-luxury-black/70 text-red-400 opacity-0 transition group-hover:opacity-100">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div>
            <x-admin.input-label for="images" value="{{ __('Add Gallery Photos') }}" />
            <input type="file" id="images" name="images[]" accept="image/*" multiple
                class="block w-full text-sm text-luxury-muted file:mr-4 file:rounded-lg file:border-0 file:bg-luxury-gold file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-luxury-black hover:file:bg-luxury-gold-light">
            <p class="mt-2 text-xs text-luxury-muted">{{ __('You can select multiple photos. Max 2MB each.') }}</p>
            <x-admin.input-error :messages="$errors->get('images')" />
        </div>
    </div>
</div>
