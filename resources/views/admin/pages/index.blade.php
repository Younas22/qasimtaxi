<x-admin.layouts.app :title="__('Pages')">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Pages') }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ __('Manage the content sections that make up your public website.') }}</p>
    </div>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($pages as $page)
            <a href="{{ route('admin.pages.edit', $page) }}" class="group rounded-2xl border border-luxury-border bg-luxury-charcoal p-6 transition hover:border-luxury-gold/40">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="font-semibold text-luxury-white group-hover:text-luxury-gold">{{ $page->name }}</p>
                        <p class="mt-1 text-xs text-luxury-muted">/{{ $page->slug === 'home' ? '' : $page->slug }}</p>
                    </div>
                    <x-admin.status-badge :active="$page->is_active" />
                </div>
                <p class="mt-4 text-sm text-luxury-muted">{{ trans_choice(':count section|:count sections', $page->sections_count, ['count' => $page->sections_count]) }}</p>
            </a>
        @endforeach
    </div>
</x-admin.layouts.app>
