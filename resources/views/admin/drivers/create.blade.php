<x-admin.layouts.app :title="__('Add Driver')">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Add Driver') }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ __('Register a new driver.') }}</p>
    </div>

    <form method="POST" action="{{ route('admin.drivers.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @include('admin.drivers._form', ['vehicles' => $vehicles])

        <div class="flex items-center gap-3">
            <x-admin.button type="submit" variant="primary">{{ __('Add Driver') }}</x-admin.button>
            <a href="{{ route('admin.drivers.index') }}" class="text-sm text-luxury-muted hover:text-luxury-white">{{ __('Cancel') }}</a>
        </div>
    </form>
</x-admin.layouts.app>
