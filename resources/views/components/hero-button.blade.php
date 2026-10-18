@props(['button', 'size' => 'full', 'deviceContext' => null])

@php
    $kind = $button['kind'] ?? 'custom';

    $label = $button['label'] ?: match ($kind) {
        'book_now' => __('Book Now'),
        'call_now' => __('Call Now'),
        'phone_number' => setting('phone'),
        'price' => __('Pricing'),
        default => __('Learn More'),
    };

    // phone_number deliberately gets no icon — the point of that button
    // kind is showing the bare number itself in place of an icon+label.
    $icon = match ($kind) {
        'book_now' => 'calendar',
        'call_now' => 'phone',
        'price' => 'cash',
        default => null,
    };

    $isPrimary = $kind === 'book_now';

    // Per-button, per-device visibility — an admin-set pair of toggles
    // rather than a single show/hide, so e.g. a phone-number button can be
    // mobile-only while Book Now stays on both.
    $showMobile = $button['show_mobile'] ?? true;
    $showDesktop = $button['show_desktop'] ?? true;

    // Hero/compact rendering (deviceContext null) needs both flags folded
    // into one responsive class, since it's the only copy of this button on
    // the page. The navbar (deviceContext 'desktop'/'mobile') instead
    // renders TWO separate copies — one inside the sticky header for
    // desktop, one in the small mobile-only strip above it (see
    // components/header.blade.php) — so each copy only needs to check its
    // own flag and can skip rendering entirely when that flag is off,
    // rather than hiding itself with CSS while still sitting in the DOM.
    if ($deviceContext === 'mobile' && ! $showMobile) {
        return;
    }

    if ($deviceContext === 'desktop' && ! $showDesktop) {
        return;
    }

    $visibilityClass = match ($deviceContext) {
        'mobile', 'desktop' => 'inline-flex',
        default => ($showMobile ? 'inline-flex' : 'hidden').' '.($showDesktop ? 'sm:inline-flex' : 'sm:hidden'),
    };

    // "nav" is a compact pill sized for the top navbar (see
    // components/header.blade.php) — no backdrop-blur (it never sits over a
    // photo), and its own coloring per kind instead of the hero's binary
    // primary/secondary look.
    //
    // "compact" and "full" (the two hero variants) use a fixed min-width
    // instead of flex-1: flex-1 makes buttons share the row equally, but
    // when one button's label is a lot longer than the others (e.g. a
    // phone number next to "Book Now"), the flex algorithm shrinks every
    // *other* button to make room for it — so a short label's button ends
    // up a different size depending on what its neighbors say. A shared
    // min-width keeps ordinary 1-2 word labels the same size regardless of
    // each other; only a genuinely long label (like a raw phone number)
    // grows past it, without shrinking its neighbors.
    $sizeClass = match ($size) {
        'nav' => 'h-10 shrink-0 gap-1.5 whitespace-nowrap rounded-lg px-2.5 text-sm font-medium sm:px-3',
        'compact' => 'min-w-[8rem] gap-1.5 whitespace-nowrap rounded-lg px-3.5 py-2.5 text-xs font-semibold sm:min-w-[9.5rem] sm:px-6 sm:py-3 sm:text-sm',
        default => 'min-w-[9.5rem] gap-1.5 whitespace-nowrap rounded-lg px-4 py-3 text-xs font-semibold sm:min-w-[11rem] sm:gap-2 sm:px-7 sm:py-3.5 sm:text-sm',
    };

    $colorClass = match (true) {
        $size === 'nav' && $isPrimary => 'bg-luxury-gold text-luxury-black transition hover:bg-luxury-gold-light active:scale-[0.98]',
        $size === 'nav' && $kind === 'price' => 'border border-luxury-gold/40 text-luxury-gold transition hover:bg-luxury-gold/10',
        $size === 'nav' => 'text-luxury-muted transition hover:bg-luxury-graphite hover:text-luxury-white',
        $isPrimary => 'bg-luxury-gold text-luxury-black transition hover:bg-luxury-gold-light active:scale-[0.98]',
        default => 'border border-luxury-white/30 bg-white/5 text-luxury-white backdrop-blur transition hover:border-luxury-white/60 hover:bg-white/10',
    };

    $classes = $visibilityClass.' items-center justify-center '.$sizeClass.' '.$colorClass;

    $url = $button['url'] ?? null;
    $isExternal = $url && str_starts_with($url, 'http');
    $ariaLabel = in_array($kind, ['call_now', 'phone_number'], true) && setting('phone')
        ? __('Call us').': '.setting('phone')
        : null;

    // Admin-picked colors (Admin → Pages → Home → Hero section) override the
    // kind's default look via inline style, which always wins over the
    // color classes above regardless of specificity — no need to strip
    // them. Left blank, a button keeps its default styling. Setting a
    // custom background also matches the border to it, so an outline-style
    // button (everything except Book Now) doesn't end up with a border in
    // one color and a fill in another.
    $customStyle = trim(
        (! empty($button['bg_color']) ? "background-color:{$button['bg_color']};border-color:{$button['bg_color']};" : '')
        .(! empty($button['text_color']) ? "color:{$button['text_color']};" : '')
    );
@endphp

@if ($kind === 'price')
    {{-- Opens the same pricing modal as the top navbar's Pricing button —
         see components/pricing-modal.blade.php and its `pricingOpen` state
         on the shared public-site Alpine shell. --}}
    <button type="button" @click="pricingOpen = true" @if ($customStyle) style="{{ $customStyle }}" @endif {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)
            <x-icon name="{{ $icon }}" class="h-4 w-4 shrink-0" />
        @endif
        {{ $label }}
    </button>
@elseif (in_array($kind, ['call_now', 'phone_number'], true))
    @if (setting('phone'))
        <a href="tel:{{ preg_replace('/[^0-9+]/', '', setting('phone')) }}" @if ($ariaLabel) aria-label="{{ $ariaLabel }}" @endif @if ($customStyle) style="{{ $customStyle }}" @endif {{ $attributes->merge(['class' => $classes]) }}>
            @if ($icon)
                <x-icon name="{{ $icon }}" class="h-4 w-4 shrink-0" />
            @endif
            {{ $label }}
        </a>
    @endif
@elseif ($url)
    <a href="{{ $isExternal ? $url : url($url) }}" @if ($isExternal) target="_blank" rel="noopener" @endif @if ($customStyle) style="{{ $customStyle }}" @endif {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)
            <x-icon name="{{ $icon }}" class="h-4 w-4 shrink-0" />
        @endif
        {{ $label }}
    </a>
@endif
