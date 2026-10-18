<x-admin.layouts.app :title="__('Add Language')">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Add Language') }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ __('Register a new language for the admin panel.') }}</p>
    </div>

    <form method="POST" action="{{ route('admin.languages.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @include('admin.languages._form')

        <div class="flex items-center gap-3">
            <x-admin.button type="submit" variant="primary">{{ __('Add Language') }}</x-admin.button>
            <a href="{{ route('admin.languages.index') }}" class="text-sm text-luxury-muted hover:text-luxury-white">{{ __('Cancel') }}</a>
        </div>
    </form>
</x-admin.layouts.app>
