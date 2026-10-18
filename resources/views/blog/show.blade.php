<x-layouts.public :title="$post->meta_title ?: $post->title" :description="$post->meta_description ?: $post->excerpt_or_summary"
    :og-image="$post->featured_image_url" og-type="article" :published-time="$post->published_at?->toIso8601String()"
    :canonical-override="$post->canonical_override" :robots-index="$post->robots_index" :robots-follow="$post->robots_follow">

    <x-slot:head>
        <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
        @if ($post->custom_schema)
            {!! $post->custom_schema !!}
        @endif
    </x-slot:head>

    <x-breadcrumbs :items="[
        ['label' => __('Home'), 'url' => route('pages.home')],
        ['label' => __('Blog'), 'url' => route('blog.index')],
        ['label' => $post->title, 'url' => null],
    ]" />
    @if ($post->featured_image_url)
        <div class="mx-auto max-w-6xl px-4 pt-12 sm:px-6 lg:px-8">
            {{-- The featured image is this page's LCP element: loaded eagerly
                 with high priority, with intrinsic width/height so the
                 layout doesn't shift while it arrives. --}}
            <img src="{{ $post->featured_image_url }}" alt="{{ $post->featured_image_alt_text }}"
                @if ($post->featured_image_small_url)
                    srcset="{{ $post->featured_image_small_url }} 960w, {{ $post->featured_image_url }} {{ $post->featured_image_size[0] ?? 1600 }}w"
                    sizes="(min-width: 1152px) 1088px, calc(100vw - 2rem)"
                @endif
                @if ($post->featured_image_size)
                    width="{{ $post->featured_image_size[0] }}" height="{{ $post->featured_image_size[1] }}"
                @endif
                fetchpriority="high" decoding="async"
                class="mx-auto h-auto max-h-[32rem] w-full rounded-2xl object-contain">
        </div>
    @endif

    <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-10 lg:grid-cols-4">
            @if (! empty($post->table_of_contents))
                <aside class="order-2 lg:order-1 lg:col-span-1">
                    <div class="toc-scroll lg:sticky lg:top-24 lg:max-h-[calc(100vh-7rem)] lg:overflow-y-auto rounded-2xl border border-luxury-border bg-luxury-graphite p-5">
                        <p class="text-sm font-semibold text-luxury-white">{{ __('Table of Contents') }}</p>
                        <nav class="mt-3 space-y-2.5 text-sm">
                            @foreach ($post->table_of_contents as $item)
                                <a href="#{{ $item['id'] }}" class="block text-luxury-muted transition hover:text-luxury-gold {{ $item['level'] === 3 ? 'ps-4 text-xs' : '' }}">
                                    {{ $item['text'] }}
                                </a>
                            @endforeach
                        </nav>
                    </div>
                </aside>
            @endif

            <article class="order-1 lg:order-2 {{ empty($post->table_of_contents) ? 'lg:col-span-4' : 'lg:col-span-3' }}">
                @if ($post->category)
                    <a href="{{ route('blog.category', $post->category->slug) }}" class="text-xs font-medium uppercase tracking-wide text-luxury-gold hover:text-luxury-gold-light">
                        {{ $post->category->name }}
                    </a>
                @endif

                <h1 class="mt-2 text-2xl font-semibold text-luxury-white sm:text-3xl">{{ $post->title }}</h1>

                <div class="mt-4 flex flex-wrap items-center gap-3 text-sm text-luxury-muted">
                    @if ($post->author)
                        <span class="flex items-center gap-2">
                            <img src="{{ $post->author->avatar_url }}" alt="{{ $post->author->name }}" class="h-6 w-6 rounded-full object-cover">
                            {{ __('By') }} {{ $post->author->name }}
                        </span>
                        <span>&middot;</span>
                    @endif
                    <time datetime="{{ $post->published_at?->toDateString() }}">{{ $post->published_at?->locale(app()->getLocale())->translatedFormat('j F Y') }}</time>
                    <span>&middot;</span>
                    <span>{{ $post->reading_time }} {{ __('min read') }}</span>
                    <span>&middot;</span>
                    <span class="flex items-center gap-1"><x-icon name="eye" class="h-4 w-4" /> {{ number_format($post->views_count) }} {{ __('views') }}</span>
                </div>

                <div class="mt-5">
                    <x-share-buttons :url="url()->current()" :title="$post->title" />
                </div>

                <div class="richtext-content mt-8 text-luxury-muted">
                    {!! resolve_internal_links($post->body_with_heading_ids) !!}
                </div>

                @if ($post->tags->isNotEmpty())
                    <div class="mt-8 flex flex-wrap items-center gap-2 border-t border-luxury-border pt-6">
                        @foreach ($post->tags as $tag)
                            <a href="{{ route('blog.tag', $tag->slug) }}" class="rounded-full border border-luxury-border px-3 py-1 text-xs text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                #{{ $tag->name }}
                            </a>
                        @endforeach
                    </div>
                @endif

                @if ($previousPost || $nextPost)
                    <div class="mt-8 grid grid-cols-1 gap-4 border-t border-luxury-border pt-6 sm:grid-cols-2">
                        @if ($previousPost)
                            <a href="{{ route('blog.show', $previousPost->slug) }}" class="group rounded-xl border border-luxury-border p-4 transition hover:border-luxury-gold/40">
                                <span class="flex items-center gap-1 text-xs text-luxury-muted">
                                    <x-icon name="chevron-right" class="h-3.5 w-3.5 rotate-180 rtl:rotate-0" />
                                    {{ __('Previous') }}
                                </span>
                                <p class="mt-1.5 line-clamp-2 text-sm font-medium text-luxury-white group-hover:text-luxury-gold">{{ $previousPost->title }}</p>
                            </a>
                        @else
                            <span></span>
                        @endif

                        @if ($nextPost)
                            <a href="{{ route('blog.show', $nextPost->slug) }}" class="group rounded-xl border border-luxury-border p-4 text-end transition hover:border-luxury-gold/40">
                                <span class="flex items-center justify-end gap-1 text-xs text-luxury-muted">
                                    {{ __('Next') }}
                                    <x-icon name="chevron-right" class="h-3.5 w-3.5 rtl:rotate-180" />
                                </span>
                                <p class="mt-1.5 line-clamp-2 text-sm font-medium text-luxury-white group-hover:text-luxury-gold">{{ $nextPost->title }}</p>
                            </a>
                        @endif
                    </div>
                @endif

                @if ($related->isNotEmpty())
                    <div class="mt-12">
                        <h2 class="mb-5 text-lg font-semibold text-luxury-white">{{ __('Related Posts') }}</h2>
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            @foreach ($related as $relatedPost)
                                <x-blog.post-card :post="$relatedPost" />
                            @endforeach
                        </div>
                    </div>
                @endif
            </article>
        </div>
    </div>

    <style>
        .toc-scroll { scrollbar-width: thin; scrollbar-color: #c9a227 transparent; }
        .toc-scroll::-webkit-scrollbar { width: 6px; }
        .toc-scroll::-webkit-scrollbar-track { background: transparent; }
        .toc-scroll::-webkit-scrollbar-thumb { background-color: #c9a227; border-radius: 9999px; }
        .toc-scroll::-webkit-scrollbar-thumb:hover { background-color: #e0b830; }
        .richtext-content h2 { font-size: 1.25rem; font-weight: 600; margin: 1.25rem 0 0.5rem; color: #f4f4f5; scroll-margin-top: 5.5rem; }
        .richtext-content h3 { font-size: 1.1rem; font-weight: 600; margin: 1.25rem 0 0.5rem; color: #f4f4f5; scroll-margin-top: 5.5rem; }
        .richtext-content p { margin: 0.75rem 0; line-height: 1.7; }
        .richtext-content ul { list-style: disc; padding-inline-start: 1.5rem; margin: 0.75rem 0; }
        .richtext-content ol { list-style: decimal; padding-inline-start: 1.5rem; margin: 0.75rem 0; }
        .richtext-content img { display: block; max-width: 100%; height: auto; margin: 1rem auto; border-radius: 0.5rem; }
        .richtext-content a { color: #c9a227; text-decoration: underline; }
        .richtext-content table { border-collapse: collapse; width: 100%; margin: 1rem 0; font-size: 0.875rem; overflow-x: auto; display: block; }
        .richtext-content th, .richtext-content td { border: 1px solid #2e2e33; padding: 0.6rem 0.9rem; text-align: start; }
        .richtext-content th { background: #1c1c1f; font-weight: 600; color: #f4f4f5; }
    </style>
</x-layouts.public>
