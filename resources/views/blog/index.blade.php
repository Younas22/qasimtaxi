@php
    $search = $search ?? null;
    $heading = $search ? __('Search Results') : (isset($activeCategory) ? $activeCategory->name : (isset($activeTag) ? '#'.$activeTag->name : __('Blog')));
    $description = isset($activeCategory) ? $activeCategory->description : null;
    $isMainIndex = ! $search && ! isset($activeCategory) && ! isset($activeTag);
    // Internal search results are thin, near-duplicate listings — keep them
    // out of the index while still letting crawlers follow the post links.
    $robotsIndex = $search ? false : null;
    // Paginated listings canonicalise to themselves (not to page 1), so
    // posts only reachable from page 2+ remain discoverable.
    $currentPage = method_exists($posts, 'currentPage') ? $posts->currentPage() : 1;
    $canonicalOverride = ! $search && $currentPage > 1 ? url()->current().'?page='.$currentPage : null;
@endphp

<x-layouts.public
    :title="$isMainIndex ? 'Taxiblog Bilzen: tips voor je taxirit en luchthavenvervoer' : $heading"
    :description="$isMainIndex ? 'Praktische tips van Qasim Taxi uit Bilzen: zo reserveer je vlot een taxi en plan je je rit naar Maastricht Aachen Airport of Brussels Airport.' : $description"
    :robots-index="$robotsIndex"
    :canonical-override="$canonicalOverride">
    <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-semibold text-luxury-white sm:text-4xl">{{ $isMainIndex ? 'Taxiblog: tips voor je rit vanuit Bilzen' : $heading }}</h1>
            @if ($isMainIndex)
                <p class="mx-auto mt-3 max-w-2xl text-luxury-muted">Praktische tips van Qasim Taxi uit Bilzen over taxi reserveren, ophaaltijden plannen en vlot op de luchthaven geraken.</p>
            @elseif ($search)
                <p class="mx-auto mt-3 max-w-2xl text-luxury-muted">{{ __('Showing results for') }} &ldquo;{{ $search }}&rdquo;</p>
            @elseif ($description)
                <p class="mx-auto mt-3 max-w-2xl text-luxury-muted">{{ $description }}</p>
            @endif
            @if (isset($activeCategory) || isset($activeTag) || $search)
                <a href="{{ route('blog.index') }}" class="mt-3 inline-block text-sm text-luxury-gold hover:text-luxury-gold-light">&larr; {{ __('Back to all posts') }}</a>
            @endif
        </div>

        <form method="GET" action="{{ route('blog.index') }}" class="mx-auto mb-10 max-w-xl">
            <div class="relative">
                <x-icon name="search" class="pointer-events-none absolute start-4 top-1/2 h-4 w-4 -translate-y-1/2 text-luxury-muted" />
                <input type="search" name="search" value="{{ $search }}" placeholder="{{ __('Search articles...') }}"
                    class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal py-3 ps-11 pe-4 text-sm text-luxury-white placeholder:text-luxury-muted/70 focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
            </div>
        </form>

        @if ($featuredPosts->isNotEmpty())
            <div class="mb-12">
                <h2 class="mb-5 text-lg font-semibold text-luxury-white">{{ __('Featured') }}</h2>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($featuredPosts as $post)
                        <x-blog.post-card :post="$post" />
                    @endforeach
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 gap-10 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    @forelse ($posts as $post)
                        <x-blog.post-card :post="$post" />
                    @empty
                        <p class="col-span-2 py-10 text-center text-luxury-muted">
                            {{ $search ? __('No articles match your search.') : __('No posts found.') }}
                        </p>
                    @endforelse
                </div>

                @if ($posts->hasPages())
                    <div class="mt-8 flex items-center justify-between text-sm text-luxury-muted">
                        <div>
                            @if ($posts->onFirstPage())
                                <span class="cursor-not-allowed rounded-lg border border-luxury-border px-3 py-1.5 opacity-40">Previous</span>
                            @else
                                <a href="{{ $posts->previousPageUrl() }}" class="rounded-lg border border-luxury-border px-3 py-1.5 hover:border-luxury-gold/40 hover:text-luxury-gold">Previous</a>
                            @endif
                        </div>
                        <p>Page {{ $posts->currentPage() }} of {{ $posts->lastPage() }}</p>
                        <div>
                            @if ($posts->hasMorePages())
                                <a href="{{ $posts->nextPageUrl() }}" class="rounded-lg border border-luxury-border px-3 py-1.5 hover:border-luxury-gold/40 hover:text-luxury-gold">Next</a>
                            @else
                                <span class="cursor-not-allowed rounded-lg border border-luxury-border px-3 py-1.5 opacity-40">Next</span>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <x-blog.sidebar :categories="$categories" :popular-posts="$popularPosts" :tags="$tags" />
        </div>
    </div>
</x-layouts.public>
