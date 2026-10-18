<x-admin.layouts.app :title="__('Add State')">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Add State') }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ __('Register a new state, province, or emirate.') }}</p>
    </div>

    <form method="POST" action="{{ route('admin.locations.states.store') }}" class="space-y-6">
        @csrf
        @include('admin.locations.states._form', ['countries' => $countries])

        <div class="flex items-center gap-3">
            <x-admin.button type="submit" variant="primary">{{ __('Add State') }}</x-admin.button>
            <a href="{{ route('admin.locations.states.index') }}" class="text-sm text-luxury-muted hover:text-luxury-white">{{ __('Cancel') }}</a>
        </div>
    </form>
</x-admin.layouts.app>
