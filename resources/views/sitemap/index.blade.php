{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach ($pages as $page)
        <url>
            <loc>{{ $page->slug === 'home' ? url('/') : url('/'.$page->slug) }}</loc>
            <lastmod>{{ $page->updated_at->toAtomString() }}</lastmod>
            <changefreq>{{ $page->slug === 'home' ? 'daily' : 'weekly' }}</changefreq>
            <priority>{{ $page->slug === 'home' ? '1.0' : '0.7' }}</priority>
        </url>
    @endforeach

    @foreach ($areas as $area)
        <url>
            <loc>{{ route('areas.show', $area->slug) }}</loc>
            <lastmod>{{ $area->updated_at->toAtomString() }}</lastmod>
            <changefreq>monthly</changefreq>
            <priority>0.6</priority>
        </url>
    @endforeach

    @if ($indexingEnabled)
        <url>
            <loc>{{ route('blog.index') }}</loc>
            <lastmod>{{ now()->toAtomString() }}</lastmod>
            <changefreq>daily</changefreq>
            <priority>0.8</priority>
        </url>
    @endif

    @foreach ($posts as $post)
        <url>
            <loc>{{ route('blog.show', $post->slug) }}</loc>
            <lastmod>{{ $post->updated_at->toAtomString() }}</lastmod>
            <changefreq>monthly</changefreq>
            <priority>0.6</priority>
        </url>
    @endforeach

    @foreach ($categories as $category)
        <url>
            <loc>{{ route('blog.category', $category->slug) }}</loc>
            <lastmod>{{ $category->updated_at->toAtomString() }}</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.5</priority>
        </url>
    @endforeach

    @foreach ($tags as $tag)
        <url>
            <loc>{{ route('blog.tag', $tag->slug) }}</loc>
            <lastmod>{{ $tag->updated_at->toAtomString() }}</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.4</priority>
        </url>
    @endforeach
</urlset>
