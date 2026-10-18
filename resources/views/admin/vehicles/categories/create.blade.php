<x-admin.layouts.app :title="__('Add Fleet Category')">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Add Fleet Category') }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ __('Create a new vehicle category.') }}</p>
    </div>

    <form method="POST" action="{{ route('admin.vehicles.categories.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @include('admin.vehicles.categories._form')

        <div class="flex items-center gap-3">
            <x-admin.button type="submit" variant="primary">{{ __('Add Category') }}</x-admin.button>
            <a href="{{ route('admin.vehicles.categories.index') }}" class="text-sm text-luxury-muted hover:text-luxury-white">{{ __('Cancel') }}</a>
        </div>
    </form>
</x-admin.layouts.app>
