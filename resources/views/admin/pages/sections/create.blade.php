<x-admin.layouts.app :title="__('Add Section — :page', ['page' => $page->name])">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Add Section') }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ $page->name }}</p>
    </div>

    <form method="POST" action="{{ route('admin.pages.sections.store', $page) }}" enctype="multipart/form-data">
        @csrf

        @include('admin.pages.sections._form', ['section' => null])

        <div class="mt-6 flex items-center justify-end gap-3">
            <a href="{{ route('admin.pages.edit', $page) }}" class="rounded-lg border border-luxury-border px-4 py-2.5 text-sm font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                {{ __('Cancel') }}
            </a>
            <x-admin.button type="submit" variant="primary">{{ __('Add Section') }}</x-admin.button>
        </div>
    </form>
</x-admin.layouts.app>
