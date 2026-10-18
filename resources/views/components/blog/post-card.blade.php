@props(['post'])

<article class="group overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal transition hover:border-luxury-gold/40">
    <a href="{{ route('blog.show', $post->slug) }}" class="block aspect-[16/9] overflow-hidden bg-luxury-graphite">
        @if ($post->featured_image_url)
            <x-lazy-image :src="$post->featured_image_url" :alt="$post->featured_image_alt_text" img-class="h-full w-full object-cover group-hover:scale-105" />
        @else
            <div class="flex h-full w-full items-center justify-center text-luxury-muted">
                <x-icon name="pencil" class="h-8 w-8" />
            </div>
        @endif
    </a>
    <div class="p-5">
        @if ($post->category)
            <a href="{{ route('blog.category', $post->category->slug) }}" class="text-xs font-medium uppercase tracking-wide text-luxury-gold hover:text-luxury-gold-light">
                {{ $post->category->name }}
            </a>
        @endif
        <h3 class="mt-2 font-semibold text-luxury-white">
            <a href="{{ route('blog.show', $post->slug) }}" class="hover:text-luxury-gold">{{ $post->title }}</a>
        </h3>
        <p class="mt-2 text-sm text-luxury-muted">{{ $post->excerpt_or_summary }}</p>

        <div class="mt-4 flex items-center gap-2 text-xs text-luxury-muted">
            @if ($post->author)
                <img src="{{ $post->author->avatar_url }}" alt="{{ $post->author->name }}" class="h-6 w-6 shrink-0 rounded-full object-cover">
            @else
                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-luxury-gold/10 text-[10px] font-semibold text-luxury-gold">A</span>
            @endif
            <span class="truncate">{{ $post->author?->name ?? setting('company_name', __('Admin')) }}</span>
            <span>&middot;</span>
            <span>{{ $post->published_at?->format('M d, Y') }}</span>
        </div>

        <div class="mt-4 flex items-center justify-between gap-3 border-t border-luxury-border pt-4">
            <span class="text-xs text-luxury-muted">{{ $post->reading_time }} {{ __('min read') }}</span>
            <a href="{{ route('blog.show', $post->slug) }}" class="inline-flex shrink-0 items-center gap-1 text-xs font-semibold text-luxury-gold hover:text-luxury-gold-light">
                {{ __('Read More') }}
                <x-icon name="chevron-right" class="h-3 w-3 rtl:rotate-180" />
            </a>
        </div>
    </div>
</article>
