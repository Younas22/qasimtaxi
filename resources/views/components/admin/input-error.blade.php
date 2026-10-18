@props(['messages'])

@if ($messages)
    <p {{ $attributes->merge(['class' => 'mt-2 text-xs text-red-400']) }}>
        {{ (is_array($messages) || $messages instanceof \Illuminate\Support\Collection) ? $messages[0] : $messages }}
    </p>
@endif
