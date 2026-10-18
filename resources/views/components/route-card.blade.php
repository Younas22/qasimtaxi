@props(['route'])

@php
    $waNumber = setting('whatsapp');
    $waDigits = $waNumber ? preg_replace('/\D+/', '', $waNumber) : null;

    $waLines = [
        __('Hi! I found this route on your website (Popular Routes) and would like to book it:'),
        '',
        __('From').': '.$route->pickup,
        __('To').': '.$route->dropoff,
    ];

    if ($route->distance) {
        $waLines[] = __('Distance').': '.rtrim(rtrim(number_format((float) $route->distance, 1), '0'), '.').' '.$route->distance_unit;
    }

    if ($route->estimated_price) {
        $waLines[] = $route->has_discount
            ? __('Estimated Price').': '.currency($route->estimated_price).' ('.__('discounted from').' '.currency($route->original_price).')'
            : __('Estimated Price').': '.currency($route->estimated_price);
    }

    $waLines[] = '';
    $waLines[] = __('Please confirm availability.');

    $waMessage = implode("\n", $waLines);

    // Popular Routes always books straight via WhatsApp, regardless of the
    // site's manual/website booking mode — these are quick, pre-priced
    // routes best confirmed by chat. No WhatsApp number configured falls
    // back to jumping to the booking widget with pickup/dropoff prefilled.
    $bookHref = $waDigits ? "https://wa.me/{$waDigits}?text=".rawurlencode($waMessage) : '#booking-widget';
@endphp

{{-- Editorial, photo-led card — big image, then plain typography, no
     border/box around it (the grid's own gap is the only separation) and no
     separate button: the whole card is one clickable link, same as the
     reference layout, just in this site's dark/gold palette instead of the
     reference's white one. --}}
<a href="{{ $bookHref }}"
    @if ($waDigits) target="_blank" rel="noopener" @else @click="window.dispatchEvent(new CustomEvent('select-route', { detail: { pickup: {{ \Illuminate\Support\Js::from($route->pickup) }}, dropoff: {{ \Illuminate\Support\Js::from($route->dropoff) }} } }))" @endif
    {{ $attributes->merge(['class' => 'group block']) }}>
    {{-- Optional — a route with no photo just shows a plain gradient tile
         instead. The hover photo (if set) crossfades in on top of the main
         one on hover — pure CSS, no JS — and only ever appears when a main
         photo exists too (see the admin form's own note on this). --}}
    <div class="relative aspect-[4/5] w-full overflow-hidden rounded-2xl bg-luxury-graphite">
        @if ($route->image_url)
            <img src="{{ $route->image_url }}" alt="{{ $route->pickup }} → {{ $route->dropoff }}"
                class="absolute inset-0 h-full w-full object-cover transition duration-300 group-hover:scale-105 {{ $route->hover_image_url ? 'group-hover:opacity-0' : '' }}">
            @if ($route->hover_image_url)
                <img src="{{ $route->hover_image_url }}" alt="{{ $route->pickup }} → {{ $route->dropoff }}"
                    class="absolute inset-0 h-full w-full object-cover opacity-0 transition duration-300 group-hover:scale-105 group-hover:opacity-100">
            @endif
        @else
            <div class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-luxury-graphite to-luxury-charcoal">
                <x-icon name="map-pin" class="h-8 w-8 text-luxury-border" />
            </div>
        @endif
    </div>

    <div class="mt-3 flex items-end justify-between gap-3">
        <div class="min-w-0">
            <p class="truncate text-base font-semibold text-luxury-white">
                {{ $route->pickup }} <span class="text-luxury-muted">&rarr;</span> {{ $route->dropoff }}
            </p>

            @if ($route->distance)
                <p class="mt-1 text-sm text-luxury-muted">
                    {{ rtrim(rtrim(number_format((float) $route->distance, 1), '0'), '.') }} {{ $route->distance_unit }}
                </p>
            @endif

            {{-- The original price's line-through and "From $X"'s underline
                 live on separate sibling spans (not nested) — putting both
                 decorations on one shared element meant only one of the two
                 text-decoration-line values could ever win, so the
                 strikethrough silently never rendered. --}}
            @if ($route->estimated_price)
                <p class="mt-1 text-sm font-semibold">
                    @if ($route->has_discount)
                        <span class="text-luxury-muted line-through">{{ currency($route->original_price) }}</span>
                    @endif
                    <span class="text-luxury-gold underline decoration-luxury-gold/40 underline-offset-2 transition group-hover:decoration-luxury-gold">
                        {{ __('From') }} {{ currency($route->estimated_price) }}
                    </span>
                </p>
            @endif
        </div>

        {{-- Purely visual — the whole card above is already the clickable
             link, so this doesn't need its own href/click handler. Small on
             purpose (per the reference layout's minimal look) but still an
             actual button shape, not just underlined text, so the CTA
             stays unmistakable. --}}
        <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-luxury-gold px-3 py-1.5 text-xs font-semibold text-luxury-black transition group-hover:bg-luxury-gold-light">
            {{ __('Book') }}
        </span>
    </div>
</a>
