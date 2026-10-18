@props(['rating' => 0, 'max' => 5, 'size' => 'h-4 w-4'])

@php
    $rating = (float) $rating;
    $starPath = 'M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.958a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.368 2.447a1 1 0 00-.363 1.118l1.287 3.957c.3.922-.755 1.688-1.538 1.118l-3.367-2.446a1 1 0 00-1.176 0l-3.367 2.446c-.783.57-1.838-.196-1.538-1.118l1.287-3.957a1 1 0 00-.363-1.118L2.062 9.385c-.783-.57-.38-1.81.588-1.81h4.163a1 1 0 00.95-.69l1.286-3.958z';
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center gap-0.5']) }}>
    @for ($i = 1; $i <= $max; $i++)
        @php $fill = max(0, min(1, $rating - ($i - 1))) * 100; @endphp
        <span class="relative inline-block {{ $size }}">
            <svg class="{{ $size }} text-luxury-border" fill="currentColor" viewBox="0 0 20 20">
                <path d="{{ $starPath }}" />
            </svg>
            <span class="absolute inset-y-0 start-0 overflow-hidden" style="width: {{ $fill }}%">
                <svg class="{{ $size }} text-luxury-gold" fill="currentColor" viewBox="0 0 20 20">
                    <path d="{{ $starPath }}" />
                </svg>
            </span>
        </span>
    @endfor
</div>
