<x-admin.layouts.app :title="__('Add Blog Category')">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Add Blog Category') }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ __('Create a new blog category.') }}</p>
    </div>

    <form method="POST" action="{{ route('admin.blog.categories.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @include('admin.blog.categories._form')

        <div class="flex items-center gap-3">
            <x-admin.button type="submit" variant="primary">{{ __('Add Category') }}</x-admin.button>
            <a href="{{ route('admin.blog.categories.index') }}" class="text-sm text-luxury-muted hover:text-luxury-white">{{ __('Cancel') }}</a>
        </div>
    </form>
</x-admin.layouts.app>
