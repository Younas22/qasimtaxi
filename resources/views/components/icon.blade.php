@props(['name'])

@php
    $path = config('icons.'.$name, config('icons.star'));
@endphp

<svg {{ $attributes->merge(['class' => 'h-5 w-5', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '1.5', 'viewBox' => '0 0 24 24']) }}>
    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}" />
</svg>
