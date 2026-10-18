@php
    $tabs = [
        ['label' => 'Countries', 'route' => 'admin.locations.countries.index'],
        ['label' => 'States', 'route' => 'admin.locations.states.index'],
        ['label' => 'Cities', 'route' => 'admin.locations.cities.index'],
        ['label' => 'Airports', 'route' => 'admin.locations.airports.index'],
        ['label' => 'Train Stations', 'route' => 'admin.locations.train-stations.index'],
        ['label' => 'Pickup Points', 'route' => 'admin.locations.pickup-points.index'],
    ];
@endphp

<div class="mb-6 flex gap-2 overflow-x-auto pb-1">
    @foreach ($tabs as $tab)
        <a href="{{ route($tab['route']) }}"
            class="shrink-0 rounded-lg px-4 py-2 text-sm font-medium transition {{ request()->routeIs($tab['route']) ? 'bg-luxury-gold/10 text-luxury-gold' : 'text-luxury-muted hover:bg-luxury-graphite hover:text-luxury-white' }}">
            {{ __($tab['label']) }}
        </a>
    @endforeach
</div>
