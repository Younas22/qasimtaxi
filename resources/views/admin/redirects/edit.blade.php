<x-admin.layouts.app :title="__('Edit Redirect')">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Edit Redirect') }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">/{{ $redirect->old_path }}</p>
    </div>

    <form method="POST" action="{{ route('admin.redirects.update', $redirect) }}" class="space-y-6">
        @csrf
        @method('PUT')
        @include('admin.redirects._form')

        <div class="flex items-center gap-3">
            <x-admin.button type="submit" variant="primary">{{ __('Update Redirect') }}</x-admin.button>
            <a href="{{ route('admin.redirects.index') }}" class="text-sm text-luxury-muted hover:text-luxury-white">{{ __('Cancel') }}</a>
        </div>
    </form>
</x-admin.layouts.app>
