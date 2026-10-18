@props([
    'action',
    'label',
    'loadingLabel' => null,
    'size' => 'md',
])

{{--
    A ride-status-changing submit button (Start Trip / Complete Trip) with a
    built-in loading state and double-submit guard. These are plain
    full-page POST-redirect forms, not AJAX — the "loading" state is just a
    disabled+spinner button shown for the brief round trip before the
    redirect lands, which is enough to stop an accidental second tap.
--}}
@php
    $sizeClasses = $size === 'lg' ? 'px-4 py-3.5 text-base' : 'px-4 py-3 text-sm';
@endphp

<form method="POST" action="{{ $action }}" {{ $attributes->merge(['class' => '']) }} x-data="{ submitting: false }" @submit="submitting = true">
    @csrf
    <button type="submit" :disabled="submitting"
        class="tap-scale flex w-full items-center justify-center gap-2 rounded-lg bg-luxury-gold {{ $sizeClasses }} font-semibold text-luxury-black transition hover:bg-luxury-gold-light disabled:opacity-70">
        <svg x-show="submitting" x-cloak class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
        <span x-text="submitting ? @js($loadingLabel ?? __('Please wait…')) : @js($label)"></span>
    </button>
</form>
