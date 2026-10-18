@props(['src', 'alt' => '', 'imgClass' => 'h-full w-full object-cover'])

<div x-data="{ loaded: false }" {{ $attributes->merge(['class' => 'relative h-full w-full overflow-hidden']) }}>
    <div class="skeleton-shimmer absolute inset-0" x-show="!loaded" x-cloak x-transition.opacity></div>
    <img src="{{ $src }}" alt="{{ $alt }}" loading="lazy"
        @load="loaded = true"
        x-init="$el.complete && (loaded = true)"
        class="{{ $imgClass }} transition duration-500"
        :class="loaded ? 'opacity-100' : 'opacity-0'">
</div>
