@props(['items'])

{{--
    $items: array<int, array{label: string, url: ?string}> — the last item
    is treated as the current page (no link, aria-current). Renders both the
    visible trail and its BreadcrumbList JSON-LD, built from the exact same
    data so the two can never drift apart.
--}}

@if (! empty($items))
    <nav aria-label="{{ __('Breadcrumb') }}" class="border-b border-luxury-border">
        <div class="mx-auto max-w-7xl px-4 py-3 sm:px-6 lg:px-8">
            <ol class="flex flex-wrap items-center gap-1.5 text-xs text-luxury-muted">
                @foreach ($items as $item)
                    <li class="flex items-center gap-1.5">
                        @if (! $loop->first)
                            <x-icon name="chevron-right" class="h-3 w-3 shrink-0 rtl:rotate-180" />
                        @endif

                        @if (! $loop->last && ! empty($item['url']))
                            <a href="{{ $item['url'] }}" class="transition hover:text-luxury-gold">{{ $item['label'] }}</a>
                        @else
                            <span aria-current="page" class="text-luxury-white">{{ $item['label'] }}</span>
                        @endif
                    </li>
                @endforeach
            </ol>
        </div>
    </nav>

    <script type="application/ld+json">{!! json_encode(app(\App\Services\SchemaBuilder::class)->breadcrumbList($items), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endif
