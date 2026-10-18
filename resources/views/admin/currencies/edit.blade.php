<x-admin.layouts.app :title="__('Edit Currency')">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Edit Currency') }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ __('Update the details for :name.', ['name' => $currency->name]) }}</p>
    </div>

    <form method="POST" action="{{ route('admin.currencies.update', $currency) }}" class="space-y-6">
        @csrf
        @method('PUT')
        @include('admin.currencies._form', ['currency' => $currency])

        <div class="flex items-center gap-3">
            <x-admin.button type="submit" variant="primary">{{ __('Save Changes') }}</x-admin.button>
            <a href="{{ route('admin.currencies.index') }}" class="text-sm text-luxury-muted hover:text-luxury-white">{{ __('Cancel') }}</a>
        </div>
    </form>
</x-admin.layouts.app>
