<x-admin.layouts.app :title="__('New Post')">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('New Post') }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ __('Write a new blog post.') }}</p>
    </div>

    <form method="POST" action="{{ route('admin.blog.store') }}" enctype="multipart/form-data">
        @csrf

        @include('admin.blog.posts._form', ['post' => null])

        <div class="mt-6 flex items-center justify-end gap-3">
            <a href="{{ route('admin.blog.index') }}" class="rounded-lg border border-luxury-border px-4 py-2.5 text-sm font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                {{ __('Cancel') }}
            </a>
            <x-admin.button type="submit" variant="primary">{{ __('Create Post') }}</x-admin.button>
        </div>
    </form>
</x-admin.layouts.app>
