@props(['vehicle', 'wide' => false])

@php
    $pricingRule = \App\Models\PricingRule::resolveForVehicle($vehicle);
    $bookingSettings = booking_setting();
    $whatsappEnabled = (bool) ($bookingSettings->manual_booking_enabled && $bookingSettings->whatsapp_number);
    $galleryImages = collect([$vehicle->image_url])
        ->merge($vehicle->images->pluck('image_url'))
        ->filter()
        ->values();
@endphp

<div {{ $attributes->merge(['class' => 'flex h-full overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal transition hover:border-luxury-gold/40 '.($wide ? 'flex-col sm:flex-row' : 'flex-col')]) }}
    x-data="{ images: {{ \Illuminate\Support\Js::from($galleryImages) }}, activeImage: 0, lightboxOpen: false, lightboxIndex: 0 }">
    {{-- Image — theme-dark-scope covers this whole block (the photo itself,
         its overlaid badges, and the thumbnail strip's "+N" overlay) so
         they stay legible against the images regardless of theme or panel.
         See app.css. --}}
    <div class="theme-dark-scope {{ $wide ? 'flex flex-col sm:w-2/5' : '' }}">
        <div class="relative aspect-[16/10] w-full shrink-0 overflow-hidden bg-luxury-graphite">
            <template x-if="images.length">
                <img :src="images[activeImage]" alt="{{ $vehicle->name }}" loading="lazy"
                    class="h-full w-full object-cover"
                    :class="images.length > 1 ? 'cursor-pointer' : ''"
                    @click="if (images.length > 1) { lightboxIndex = activeImage; lightboxOpen = true; }">
            </template>
            <template x-if="!images.length">
                <div class="flex h-full w-full items-center justify-center">
                    <x-icon name="car" class="h-10 w-10 text-luxury-muted" />
                </div>
            </template>

            @if ($vehicle->category)
                <span class="absolute start-3 top-3 rounded-full bg-luxury-black/70 px-2.5 py-1 text-[11px] font-medium uppercase tracking-wide text-luxury-gold backdrop-blur">
                    {{ $vehicle->category->name }}
                </span>
            @endif

            <div class="absolute end-3 top-3 flex flex-col items-end gap-2">
                @auth('customer')
                    @php $isFavorited = auth('customer')->user()->hasFavorited($vehicle); @endphp
                    <form method="POST" action="{{ route('customer.favorites.toggle', $vehicle) }}">
                        @csrf
                        <button type="submit" aria-label="{{ __('Toggle favorite') }}"
                            class="tap-scale flex h-8 w-8 items-center justify-center rounded-full bg-luxury-black/70 backdrop-blur transition {{ $isFavorited ? 'text-luxury-gold' : 'text-luxury-white hover:text-luxury-gold' }}">
                            <x-icon name="heart" class="h-4 w-4" />
                        </button>
                    </form>
                @endauth

                @if ($vehicle->average_rating)
                    <span class="flex items-center gap-1 rounded-full bg-luxury-black/70 px-2.5 py-1 text-[11px] font-medium text-luxury-white backdrop-blur">
                        <x-icon name="star" class="h-3 w-3 text-luxury-gold" />
                        {{ $vehicle->average_rating }}
                    </span>
                @endif
            </div>

            {{-- Photo count badge — click opens the lightbox. --}}
            <template x-if="images.length > 1">
                <button type="button" @click="lightboxIndex = activeImage; lightboxOpen = true"
                    class="absolute bottom-3 end-3 flex cursor-pointer items-center gap-1 rounded-full bg-luxury-black/70 px-2.5 py-1 text-[11px] font-medium text-luxury-white backdrop-blur transition hover:text-luxury-gold">
                    <x-icon name="eye" class="h-3 w-3" />
                    <span x-text="images.length"></span>
                </button>
            </template>
        </div>

        @if ($wide)
            {{-- Thumbnail strip: up to 4 slots, click to swap the main image; the
                 4th slot becomes a "+N" overlay opening the lightbox once there
                 are more than 4 images to browse. --}}
            <template x-if="images.length > 1">
                <div class="flex gap-2 p-3">
                    <template x-for="(img, idx) in images.slice(0, 4)" :key="idx">
                        <button type="button"
                            @click="(idx === 3 && images.length > 4) ? (lightboxIndex = idx, lightboxOpen = true) : (activeImage = idx)"
                            class="relative h-14 w-14 shrink-0 cursor-pointer overflow-hidden rounded-lg border transition"
                            :class="activeImage === idx ? 'border-luxury-gold' : 'border-luxury-border hover:border-luxury-gold/40'">
                            <img :src="img" alt="" class="h-full w-full object-cover">
                            <template x-if="idx === 3 && images.length > 4">
                                <div class="absolute inset-0 flex items-center justify-center bg-luxury-black/70 text-xs font-semibold text-luxury-white"
                                    x-text="'+' + (images.length - 4)"></div>
                            </template>
                        </button>
                    </template>
                </div>
            </template>
        @endif
    </div>

    {{-- Lightbox — a full-screen photo viewer, theme-invariant like any
         photo lightbox regardless of theme or panel. --}}
    <template x-if="lightboxOpen">
        <div class="theme-dark-scope fixed inset-0 z-50 flex items-center justify-center bg-luxury-black/95 p-4"
            @click.self="lightboxOpen = false" @keydown.window.escape="lightboxOpen = false">
            <button type="button" @click="lightboxOpen = false" aria-label="{{ __('Close') }}"
                class="absolute end-4 top-4 flex h-10 w-10 cursor-pointer items-center justify-center rounded-full bg-luxury-charcoal text-luxury-white transition hover:text-luxury-gold">
                <x-icon name="close" class="h-5 w-5" />
            </button>

            <template x-if="images.length > 1">
                <button type="button" @click="lightboxIndex = (lightboxIndex - 1 + images.length) % images.length" aria-label="{{ __('Previous') }}"
                    class="absolute start-4 top-1/2 flex h-10 w-10 -translate-y-1/2 cursor-pointer items-center justify-center rounded-full bg-luxury-charcoal text-luxury-white transition hover:text-luxury-gold">
                    <x-icon name="chevron-left" class="h-5 w-5" />
                </button>
            </template>

            <img :src="images[lightboxIndex]" alt="{{ $vehicle->name }}" class="max-h-[85vh] max-w-full rounded-lg object-contain">

            <template x-if="images.length > 1">
                <button type="button" @click="lightboxIndex = (lightboxIndex + 1) % images.length" aria-label="{{ __('Next') }}"
                    class="absolute end-4 top-1/2 flex h-10 w-10 -translate-y-1/2 cursor-pointer items-center justify-center rounded-full bg-luxury-charcoal text-luxury-white transition hover:text-luxury-gold">
                    <x-icon name="chevron-right" class="h-5 w-5" />
                </button>
            </template>

            <template x-if="images.length > 1">
                <span class="absolute bottom-4 rounded-full bg-luxury-black/70 px-3 py-1 text-xs text-luxury-white" x-text="(lightboxIndex + 1) + ' / ' + images.length"></span>
            </template>
        </div>
    </template>

    {{-- Body --}}
    <div class="flex flex-1 flex-col p-5 {{ $wide ? 'sm:justify-center sm:p-8' : '' }}">
        <p class="font-semibold text-luxury-white">{{ $vehicle->name }}</p>
        <p class="text-xs text-luxury-muted">{{ $vehicle->brand }} {{ $vehicle->model }} &middot; {{ $vehicle->year }}</p>

        {{-- Seats / Luggage --}}
        <div class="mt-4 grid grid-cols-2 gap-2 text-center text-xs text-luxury-muted">
            <div class="flex items-center justify-center gap-1.5 rounded-lg bg-luxury-graphite py-2">
                <x-icon name="users" class="h-3.5 w-3.5" />
                <span class="font-semibold text-luxury-white">{{ $vehicle->seats }}</span> {{ __('Seats') }}
            </div>
            <div class="flex items-center justify-center gap-1.5 rounded-lg bg-luxury-graphite py-2">
                <x-icon name="briefcase" class="h-3.5 w-3.5" />
                <span class="font-semibold text-luxury-white">{{ $vehicle->luggage }}</span> {{ __('Bags') }}
            </div>
        </div>

        {{-- Features --}}
        @if (! empty($vehicle->feature_list))
            <div class="mt-3 flex flex-wrap gap-1.5">
                @foreach ($vehicle->feature_list as $feature)
                    <span class="rounded-full bg-luxury-graphite px-2.5 py-1 text-[11px] text-luxury-muted">{{ $feature }}</span>
                @endforeach
            </div>
        @endif

        {{-- Pricing --}}
        <div class="mt-4 flex items-center justify-between border-t border-luxury-border pt-4">
            <div>
                <p class="text-[11px] uppercase tracking-wide text-luxury-muted">{{ __('Starting from') }}</p>
                <p class="text-lg font-semibold text-luxury-gold">{{ currency($pricingRule->base_fare) }}</p>
            </div>

            @if ($whatsappEnabled)
                {{-- Manual booking mode: skip the form entirely, go straight to WhatsApp. --}}
                @php
                    $waMessage = __("Hi! I'd like to book the :vehicle (:category). Please confirm availability.", [
                        'vehicle' => $vehicle->name,
                        'category' => $vehicle->category->name ?? __('Any Vehicle'),
                    ]);
                    $waDigits = preg_replace('/\D/', '', $bookingSettings->whatsapp_number);
                @endphp
                <a href="https://wa.me/{{ $waDigits }}?text={{ urlencode($waMessage) }}" target="_blank" rel="noopener"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-luxury-gold px-4 py-2.5 text-sm font-semibold text-luxury-black transition hover:bg-luxury-gold-light active:scale-[0.98]">
                    <x-icon name="chat" class="h-4 w-4" />
                    {{ __('Book') }}
                </a>
            @else
                {{-- Website booking mode: smooth-scrolls to the booking widget
                     and pre-selects this vehicle's category via a shared browser event. --}}
                <a href="#booking-widget"
                    @click="window.dispatchEvent(new CustomEvent('select-vehicle-category', { detail: '{{ $vehicle->vehicle_category_id }}' }))"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-luxury-gold px-4 py-2.5 text-sm font-semibold text-luxury-black transition hover:bg-luxury-gold-light active:scale-[0.98]">
                    <x-icon name="calendar" class="h-4 w-4" />
                    {{ __('Book') }}
                </a>
            @endif
        </div>
    </div>
</div>
