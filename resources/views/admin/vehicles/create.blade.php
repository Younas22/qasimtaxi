<x-admin.layouts.app :title="__('Add Vehicle')">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Add Vehicle') }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ __('Register a new vehicle in your fleet.') }}</p>
    </div>

    <form method="POST" action="{{ route('admin.vehicles.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @include('admin.vehicles._form', ['categories' => $categories])

        <div class="flex items-center gap-3">
            <x-admin.button type="submit" variant="primary">{{ __('Add Vehicle') }}</x-admin.button>
            <a href="{{ route('admin.vehicles.index') }}" class="text-sm text-luxury-muted hover:text-luxury-white">{{ __('Cancel') }}</a>
        </div>
    </form>
</x-admin.layouts.app>
