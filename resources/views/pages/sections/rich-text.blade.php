@props(['section'])

<section class="border-b border-luxury-border">
    <div class="mx-auto max-w-3xl px-4 py-16 text-center sm:px-6 lg:px-8">
        @if ($section->heading)
            <{{ $headingTag ?? 'h2' }} class="text-2xl font-semibold text-luxury-white sm:text-3xl">{{ __($section->heading) }}</{{ $headingTag ?? 'h2' }}>
        @endif
        <div class="richtext-content mt-4 text-luxury-muted">
            {!! resolve_internal_links(__($section->body)) !!}
        </div>
    </div>
</section>

<style>
    .richtext-content h2 { font-size: 1.25rem; font-weight: 600; margin: 1rem 0 0.5rem; color: #f4f4f5; }
    .richtext-content h3 { font-size: 1.1rem; font-weight: 600; margin: 1rem 0 0.5rem; color: #f4f4f5; }
    .richtext-content p { margin: 0.75rem 0; line-height: 1.7; }
    .richtext-content ul { list-style: disc; padding-inline-start: 1.5rem; margin: 0.75rem 0; }
    .richtext-content ol { list-style: decimal; padding-inline-start: 1.5rem; margin: 0.75rem 0; }
    .richtext-content a { color: #c9a227; text-decoration: underline; }
</style>
