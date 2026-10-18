@php
    $breadcrumbItems = null;

    if ($page->slug !== 'home') {
        $breadcrumbItems = [['label' => __('Home'), 'url' => route('pages.home')]];

        if (\App\Models\Page::isServiceLike($page->slug) && \Illuminate\Support\Facades\Route::has('pages.show')) {
            $breadcrumbItems[] = ['label' => __('Services'), 'url' => route('pages.show', 'services')];
        }

        $breadcrumbItems[] = ['label' => __($page->name), 'url' => null];
    }

    // A page needs exactly one H1. Hero and contact-info sections render
    // their own; on every other page (About, Services, FAQ, Areas, legal
    // pages...) the first section's heading is promoted to the H1 so the
    // page still has a clear primary heading without a layout change.
    $promoteFirstHeading = ! $sections->contains(fn ($s) => in_array($s->type, ['hero', 'contact_info'], true));

    $schemaBuilder = app(\App\Services\SchemaBuilder::class);
    $pageSchemas = array_filter([
        $schemaBuilder->webPage($page->meta_title ?: $page->name, $page->meta_description, $page->canonical_override),
        \App\Models\Page::isServiceLike($page->slug)
            ? $schemaBuilder->service(__($page->name), $page->meta_description, $page->canonical_override)
            : null,
    ]);
@endphp

<x-layouts.public
    :title="$page->meta_title ?: $page->name"
    :description="$page->meta_description"
    :current-slug="$page->slug"
    :nav-pages="$navPages"
    :og-image="$page->og_image_url"
    :canonical-override="$page->canonical_override"
    :robots-index="$page->robots_index"
    :robots-follow="$page->robots_follow"
>
    <x-slot:head>
        @foreach ($pageSchemas as $pageSchema)
            <script type="application/ld+json">{!! json_encode($pageSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
        @endforeach
        @if ($page->custom_schema)
            {!! $page->custom_schema !!}
        @endif
    </x-slot:head>

    @if ($breadcrumbItems)
        <x-breadcrumbs :items="$breadcrumbItems" />
    @endif

    @forelse ($sections as $section)
        @include('pages.sections.'.str_replace('_', '-', $section->type), [
            'section' => $section,
            'page' => $page,
            'headingTag' => $promoteFirstHeading && $loop->first ? 'h1' : 'h2',
        ])
    @empty
        <div class="mx-auto max-w-3xl px-4 py-24 text-center sm:px-6 lg:px-8">
            <h1 class="text-2xl font-semibold text-luxury-white">{{ $page->name }}</h1>
            <p class="mt-3 text-luxury-muted">This page doesn't have any content yet.</p>
        </div>
    @endforelse
</x-layouts.public>
