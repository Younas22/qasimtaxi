@props(['section'])

@php
    $settings = $section->route_settings;
    $groups = \App\Models\RouteType::active()->ordered()->get()->map(fn ($routeType) => [
        'label' => $routeType->name,
        'routes' => \App\Models\PopularRoute::active()->ofType($routeType->id)->latest()->limit($settings['limit'])->get(),
    ])->filter(fn ($group) => $group['routes']->isNotEmpty())->values();
@endphp

@if ($groups->isNotEmpty())
    <section class="border-b border-luxury-border">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            @if ($section->heading || $section->subheading)
                <div class="mx-auto max-w-2xl text-center">
                    @if ($section->heading)
                        <h2 class="text-2xl font-semibold text-luxury-white sm:text-3xl">{{ __($section->heading) }}</h2>
                    @endif
                    @if ($section->subheading)
                        <p class="mt-3 text-luxury-muted">{{ __($section->subheading) }}</p>
                    @endif
                </div>
            @endif

            <div class="mt-10 space-y-12">
                @foreach ($groups as $group)
                    <div>
                        <h3 class="text-lg font-semibold text-luxury-white">{{ __($group['label'].'s') }}</h3>

                        <div class="mt-5 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($group['routes'] as $route)
                                <x-route-card :route="$route" />
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
