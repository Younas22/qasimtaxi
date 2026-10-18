<x-admin.layouts.app :title="__('Edit Post')">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Edit Post') }}</h2>
            <p class="mt-1 text-sm text-luxury-muted">{{ $post->title }}</p>
        </div>
        @if ($post->status === 'published')
            <a href="{{ route('blog.show', $post->slug) }}" target="_blank" rel="noopener" class="text-sm text-luxury-gold hover:text-luxury-gold-light">
                {{ __('View Live') }} &rarr;
            </a>
        @endif
    </div>

    <form method="POST" action="{{ route('admin.blog.update', $post) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        @include('admin.blog.posts._form', ['post' => $post])

        <div class="mt-6 flex items-center justify-end gap-3">
            <a href="{{ route('admin.blog.index') }}" class="rounded-lg border border-luxury-border px-4 py-2.5 text-sm font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                {{ __('Cancel') }}
            </a>
            <x-admin.button type="submit" variant="primary">{{ __('Update Post') }}</x-admin.button>
        </div>
    </form>
</x-admin.layouts.app>
