<x-admin.layouts.app :title="__('Add Pickup Point')">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Add Pickup Point') }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ __('Register a new named pickup point.') }}</p>
    </div>

    <form method="POST" action="{{ route('admin.locations.pickup-points.store') }}" class="space-y-6">
        @csrf
        @include('admin.locations.pickup-points._form', ['cities' => $cities, 'types' => $types])

        <div class="flex items-center gap-3">
            <x-admin.button type="submit" variant="primary">{{ __('Add Pickup Point') }}</x-admin.button>
            <a href="{{ route('admin.locations.pickup-points.index') }}" class="text-sm text-luxury-muted hover:text-luxury-white">{{ __('Cancel') }}</a>
        </div>
    </form>
</x-admin.layouts.app>
