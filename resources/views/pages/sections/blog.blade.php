@props(['section'])

@php
    $settings = $section->blog_settings;
    $featuredPosts = \App\Models\BlogPost::published()->featured()->with('category')->latest('published_at')->limit(3)->get();
    $latestPosts = \App\Models\BlogPost::published()->with('category')
        ->when($featuredPosts->isNotEmpty(), fn ($q) => $q->whereNotIn('id', $featuredPosts->pluck('id')))
        ->latest('published_at')
        ->limit($settings['limit'])
        ->get();
@endphp

@if ($featuredPosts->isNotEmpty() || $latestPosts->isNotEmpty())
    <section class="border-b border-luxury-border">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div class="mx-auto max-w-2xl text-center sm:mx-0 sm:text-start">
                    @if ($section->heading)
                        <h2 class="text-2xl font-semibold text-luxury-white sm:text-3xl">{{ __($section->heading) }}</h2>
                    @endif
                    @if ($section->subheading)
                        <p class="mt-3 text-luxury-muted">{{ __($section->subheading) }}</p>
                    @endif
                </div>

                <a href="{{ route('blog.index') }}" class="mx-auto inline-flex shrink-0 items-center gap-1 text-sm font-semibold text-luxury-gold hover:text-luxury-gold-light sm:mx-0">
                    {{ __('View All Articles') }}
                    <x-icon name="chevron-right" class="h-4 w-4 rtl:rotate-180" />
                </a>
            </div>

            @if ($featuredPosts->isNotEmpty())
                <div class="mt-10">
                    <h3 class="mb-5 text-xs font-semibold uppercase tracking-wide text-luxury-gold">{{ __('Featured') }}</h3>
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($featuredPosts as $post)
                            <x-blog.post-card :post="$post" />
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($latestPosts->isNotEmpty())
                <div class="mt-12">
                    <h3 class="mb-5 text-xs font-semibold uppercase tracking-wide text-luxury-muted">{{ __('Latest Articles') }}</h3>
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($latestPosts as $post)
                            <x-blog.post-card :post="$post" />
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>
@endif
