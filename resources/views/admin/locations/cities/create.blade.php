<x-admin.layouts.app :title="__('Add City')">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Add City') }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ __('Register a new city.') }}</p>
    </div>

    <form method="POST" action="{{ route('admin.locations.cities.store') }}" class="space-y-6">
        @csrf
        @include('admin.locations.cities._form', ['countries' => $countries, 'states' => $states])

        <div class="flex items-center gap-3">
            <x-admin.button type="submit" variant="primary">{{ __('Add City') }}</x-admin.button>
            <a href="{{ route('admin.locations.cities.index') }}" class="text-sm text-luxury-muted hover:text-luxury-white">{{ __('Cancel') }}</a>
        </div>
    </form>
</x-admin.layouts.app>
