@props(['section'])

@php
    $settings = $section->fleet_settings;
    $vehicles = \App\Models\Vehicle::active()->with(['category', 'images'])
        ->when($settings['category_id'], fn ($q) => $q->where('vehicle_category_id', $settings['category_id']))
        ->latest()->limit($settings['limit'])->get();
    $categories = \App\Models\VehicleCategory::active()->ordered()->get()
        ->filter(fn ($category) => $vehicles->contains('vehicle_category_id', $category->id))
        ->values();
    $searchIndex = $vehicles->map(fn ($vehicle) => [
        'id' => $vehicle->id,
        'category' => (string) $vehicle->vehicle_category_id,
        'haystack' => strtolower($vehicle->name.' '.$vehicle->brand.' '.$vehicle->model),
    ]);
    // A handful of vehicles don't fill a slider row — a scroll-snap slider
    // with working arrows needs enough cards to actually scroll past. Below
    // that, a centered wrap layout looks intentional instead of sparse. With
    // only 1-2 vehicles (a single-car operator), a full-width wide card with
    // the image on one side and details on the other reads better than a
    // small narrow card floating alone on a wide screen.
    $vehicleCount = $vehicles->count();
    $layout = $vehicleCount > 3 ? 'slider' : ($vehicleCount <= 2 ? 'wide' : 'grid');
    // Written out in full so Tailwind's build-time class scanner (which only
    // matches literal text, not interpolated strings) picks these up.
    $wideColSpanClass = $vehicleCount === 1 ? 'lg:col-span-12' : 'lg:col-span-6';
@endphp

@if ($vehicles->isNotEmpty())
    <section class="border-b border-luxury-border" x-data="fleetShowcase({{ \Illuminate\Support\Js::from($searchIndex) }})">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            @if ($section->heading || $section->subheading)
                <div class="mx-auto max-w-2xl text-center">
                    @if ($section->heading)
                        <h2 class="text-2xl font-semibold text-luxury-white sm:text-3xl">{{ __($section->heading) }}</h2>
                    @endif
                    @if ($section->subheading)
                        <p class="mt-3 text-luxury-muted">{{ __($section->subheading) }}</p>
                    @endif
                </div>
            @endif

            {{-- Filter + Search --}}
            <div class="mt-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-wrap gap-2">
                    <button type="button" @click="activeCategory = 'all'"
                        class="rounded-full border px-4 py-2 text-xs font-medium transition"
                        :class="activeCategory === 'all' ? 'border-luxury-gold bg-luxury-gold/10 text-luxury-gold' : 'border-luxury-border text-luxury-muted hover:border-luxury-gold/40 hover:text-luxury-gold'">
                        {{ __('All') }}
                    </button>
                    @foreach ($categories as $category)
                        <button type="button" @click="activeCategory = '{{ $category->id }}'"
                            class="rounded-full border px-4 py-2 text-xs font-medium transition"
                            :class="activeCategory === '{{ $category->id }}' ? 'border-luxury-gold bg-luxury-gold/10 text-luxury-gold' : 'border-luxury-border text-luxury-muted hover:border-luxury-gold/40 hover:text-luxury-gold'">
                            {{ $category->name }}
                        </button>
                    @endforeach
                </div>

                <div class="relative sm:w-64">
                    <x-icon name="search" class="pointer-events-none absolute start-3 top-1/2 h-4 w-4 -translate-y-1/2 text-luxury-muted" />
                    <input type="search" x-model="search" placeholder="{{ __('Search vehicles...') }}"
                        class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal py-2.5 ps-9 pe-3 text-sm text-luxury-white placeholder:text-luxury-muted/70 focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                </div>
            </div>

            @if ($layout === 'slider')
                {{-- Slider --}}
                <div class="relative mt-8">
                    <button type="button" @click="scrollPrev" aria-label="{{ __('Previous') }}"
                        class="absolute -start-4 top-1/2 z-10 hidden h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full border border-luxury-border bg-luxury-charcoal text-luxury-muted shadow-lg transition hover:border-luxury-gold/40 hover:text-luxury-gold lg:flex">
                        <x-icon name="chevron-left" class="h-4 w-4" />
                    </button>

                    <div x-ref="slider" class="scrollbar-luxury flex snap-x snap-mandatory gap-5 overflow-x-auto scroll-smooth pb-4">
                        @foreach ($vehicles as $vehicle)
                            <div class="w-[85%] shrink-0 snap-start sm:w-[46%] lg:w-[31%]"
                                x-show="matches({{ $vehicle->id }})">
                                <x-vehicle-card :vehicle="$vehicle" />
                            </div>
                        @endforeach
                    </div>

                    <button type="button" @click="scrollNext" aria-label="{{ __('Next') }}"
                        class="absolute -end-4 top-1/2 z-10 hidden h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full border border-luxury-border bg-luxury-charcoal text-luxury-muted shadow-lg transition hover:border-luxury-gold/40 hover:text-luxury-gold lg:flex">
                        <x-icon name="chevron-right" class="h-4 w-4" />
                    </button>
                </div>
            @elseif ($layout === 'wide')
                {{-- 1-2 vehicles: full-width cards, image on one side and details on the other. --}}
                <div class="mt-8 grid grid-cols-1 gap-5 lg:grid-cols-12">
                    @foreach ($vehicles as $vehicle)
                        <div class="{{ $wideColSpanClass }}"
                            x-show="matches({{ $vehicle->id }})">
                            <x-vehicle-card :vehicle="$vehicle" :wide="true" />
                        </div>
                    @endforeach
                </div>
            @else
                {{-- A few vehicles: center them instead of using a slider built for scrolling past many. --}}
                <div class="mt-8 flex flex-wrap justify-center gap-5">
                    @foreach ($vehicles as $vehicle)
                        <div class="w-full max-w-sm sm:w-[46%] lg:w-[31%]"
                            x-show="matches({{ $vehicle->id }})">
                            <x-vehicle-card :vehicle="$vehicle" />
                        </div>
                    @endforeach
                </div>
            @endif

            <p x-show="matchingCount() === 0" x-cloak class="mt-6 text-center text-sm text-luxury-muted">
                {{ __('No vehicles match your search.') }}
            </p>
        </div>
    </section>
@endif

<script>
    function fleetShowcase(index) {
        return {
            activeCategory: 'all',
            search: '',
            index,

            matches(id) {
                const entry = this.index.find((v) => v.id === id);
                if (!entry) return false;

                const categoryOk = this.activeCategory === 'all' || entry.category === this.activeCategory;
                const searchOk = !this.search || entry.haystack.includes(this.search.toLowerCase());

                return categoryOk && searchOk;
            },

            matchingCount() {
                return this.index.filter((entry) => this.matches(entry.id)).length;
            },

            // Slider scrolling assumes LTR reading order (native touch swipe
            // works correctly in RTL too — only these desktop arrow buttons
            // are LTR-oriented).
            scrollPrev() {
                this.$refs.slider.scrollBy({ left: -360, behavior: 'smooth' });
            },
            scrollNext() {
                this.$refs.slider.scrollBy({ left: 360, behavior: 'smooth' });
            },
        };
    }
</script>
