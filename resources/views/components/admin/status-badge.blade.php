@props(['active'])

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium '.($active ? 'bg-emerald-500/15 text-emerald-400' : 'bg-red-500/15 text-red-400')]) }}>
    <span class="h-1.5 w-1.5 rounded-full {{ $active ? 'bg-emerald-400' : 'bg-red-400' }}"></span>
    {{ $active ? __('Active') : __('Disabled') }}
</span>
