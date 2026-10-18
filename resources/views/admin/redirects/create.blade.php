<x-admin.layouts.app :title="__('Add Redirect')">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Add Redirect') }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ __('Send an old URL to its new location.') }}</p>
    </div>

    <form method="POST" action="{{ route('admin.redirects.store') }}" class="space-y-6">
        @csrf
        @include('admin.redirects._form')

        <div class="flex items-center gap-3">
            <x-admin.button type="submit" variant="primary">{{ __('Add Redirect') }}</x-admin.button>
            <a href="{{ route('admin.redirects.index') }}" class="text-sm text-luxury-muted hover:text-luxury-white">{{ __('Cancel') }}</a>
        </div>
    </form>
</x-admin.layouts.app>
