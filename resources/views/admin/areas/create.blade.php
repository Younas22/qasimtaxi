<x-admin.layouts.app :title="__('Add Area')">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Add Area') }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ __('Add a town or city to your list of service areas.') }}</p>
    </div>

    <form method="POST" action="{{ route('admin.areas.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @include('admin.areas._form')

        <div class="flex items-center gap-3">
            <x-admin.button type="submit" variant="primary">{{ __('Add Area') }}</x-admin.button>
            <a href="{{ route('admin.areas.index') }}" class="text-sm text-luxury-muted hover:text-luxury-white">{{ __('Cancel') }}</a>
        </div>
    </form>
</x-admin.layouts.app>
