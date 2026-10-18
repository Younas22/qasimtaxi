<x-admin.layouts.app :title="__('Add Country')">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Add Country') }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ __('Register a new country.') }}</p>
    </div>

    <form method="POST" action="{{ route('admin.locations.countries.store') }}" class="space-y-6">
        @csrf
        @include('admin.locations.countries._form')

        <div class="flex items-center gap-3">
            <x-admin.button type="submit" variant="primary">{{ __('Add Country') }}</x-admin.button>
            <a href="{{ route('admin.locations.countries.index') }}" class="text-sm text-luxury-muted hover:text-luxury-white">{{ __('Cancel') }}</a>
        </div>
    </form>
</x-admin.layouts.app>
