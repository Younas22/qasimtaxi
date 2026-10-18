@props(['categories', 'popularPosts', 'tags'])

<aside class="space-y-6">
    {{-- Categories --}}
    <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-5">
        <h3 class="text-sm font-semibold text-luxury-white">{{ __('Categories') }}</h3>
        <ul class="mt-3 space-y-2">
            @foreach ($categories as $category)
                <li>
                    <a href="{{ route('blog.category', $category->slug) }}" class="flex items-center justify-between text-sm text-luxury-muted hover:text-luxury-gold">
                        <span>{{ $category->name }}</span>
                        <span class="text-xs text-luxury-muted">{{ $category->posts_count }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>

    {{-- Popular Posts --}}
    @if ($popularPosts->isNotEmpty())
        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-5">
            <h3 class="text-sm font-semibold text-luxury-white">{{ __('Popular Posts') }}</h3>
            <ul class="mt-3 space-y-3">
                @foreach ($popularPosts as $popular)
                    <li>
                        <a href="{{ route('blog.show', $popular->slug) }}" class="flex items-center gap-3 group">
                            <div class="flex h-12 w-16 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-luxury-border bg-luxury-graphite">
                                @if ($popular->featured_image_url)
                                    <img src="{{ $popular->featured_image_url }}" alt="" class="h-full w-full object-cover">
                                @else
                                    <x-icon name="pencil" class="h-4 w-4 text-luxury-muted" />
                                @endif
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-sm text-luxury-white group-hover:text-luxury-gold">{{ $popular->title }}</p>
                                <p class="mt-0.5 flex items-center gap-1 text-xs text-luxury-muted">
                                    <x-icon name="eye" class="h-3.5 w-3.5" />
                                    {{ number_format($popular->views_count) }} {{ __('views') }}
                                </p>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Tags --}}
    @if ($tags->isNotEmpty())
        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-5">
            <h3 class="text-sm font-semibold text-luxury-white">{{ __('Tags') }}</h3>
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach ($tags as $tag)
                    <a href="{{ route('blog.tag', $tag->slug) }}" class="rounded-full border border-luxury-border px-3 py-1 text-xs text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                        {{ $tag->name }}
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</aside>
