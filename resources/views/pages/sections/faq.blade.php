@props(['section'])

@php
    $items = collect($section->faq_items)->values();
    $categories = $items->pluck('category')->filter()->unique()->values();
    $searchIndex = $items->map(fn ($item, $index) => [
        'index' => $index,
        'category' => $item['category'] ?? '',
        'haystack' => strtolower(($item['question'] ?? '').' '.strip_tags($item['answer'] ?? '')),
    ]);
@endphp

@if ($items->isNotEmpty())
    <section class="border-b border-luxury-border" x-data="faqAccordion({{ \Illuminate\Support\Js::from($searchIndex) }})">
        <div class="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8">
            @if ($section->heading || $section->subheading)
                <div class="text-center">
                    @if ($section->heading)
                        <{{ $headingTag ?? 'h2' }} class="text-2xl font-semibold text-luxury-white sm:text-3xl">{{ __($section->heading) }}</{{ $headingTag ?? 'h2' }}>
                    @endif
                    @if ($section->subheading)
                        <p class="mt-3 text-luxury-muted">{{ __($section->subheading) }}</p>
                    @endif
                </div>
            @endif

            {{-- Search --}}
            <div class="relative mt-8">
                <x-icon name="search" class="pointer-events-none absolute start-4 top-1/2 h-4 w-4 -translate-y-1/2 text-luxury-muted" />
                <input type="search" x-model="search" placeholder="{{ __('Search questions...') }}"
                    class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal py-3 ps-11 pe-4 text-sm text-luxury-white placeholder:text-luxury-muted/70 focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
            </div>

            {{-- Category filters --}}
            @if ($categories->isNotEmpty())
                <div class="mt-4 flex flex-wrap justify-center gap-2">
                    <button type="button" @click="activeCategory = 'all'"
                        class="rounded-full border px-4 py-2 text-xs font-medium transition"
                        :class="activeCategory === 'all' ? 'border-luxury-gold bg-luxury-gold/10 text-luxury-gold' : 'border-luxury-border text-luxury-muted hover:border-luxury-gold/40 hover:text-luxury-gold'">
                        {{ __('All') }}
                    </button>
                    @foreach ($categories as $category)
                        <button type="button" @click="activeCategory = {{ \Illuminate\Support\Js::from($category) }}"
                            class="rounded-full border px-4 py-2 text-xs font-medium transition"
                            :class="activeCategory === {{ \Illuminate\Support\Js::from($category) }} ? 'border-luxury-gold bg-luxury-gold/10 text-luxury-gold' : 'border-luxury-border text-luxury-muted hover:border-luxury-gold/40 hover:text-luxury-gold'">
                            {{ __($category) }}
                        </button>
                    @endforeach
                </div>
            @endif

            <div class="mt-8 space-y-3">
                @foreach ($items as $index => $item)
                    <div x-show="matches({{ $index }})" class="rounded-xl border border-luxury-border bg-luxury-charcoal">
                        <button type="button" @click="toggle({{ $index }})"
                            class="flex w-full items-center justify-between gap-4 px-5 py-4 text-start">
                            <span>
                                <span class="font-medium text-luxury-white">{{ __($item['question'] ?? '') }}</span>
                                @if (! empty($item['category']))
                                    <span class="ms-2 rounded-full bg-luxury-gold/10 px-2 py-0.5 text-[11px] font-medium uppercase tracking-wide text-luxury-gold">{{ __($item['category']) }}</span>
                                @endif
                            </span>
                            <x-icon name="chevron-down" class="h-4 w-4 shrink-0 text-luxury-muted transition-transform" x-bind:class="open === {{ $index }} ? 'rotate-180' : ''" />
                        </button>
                        <div x-show="open === {{ $index }}" x-cloak x-transition class="px-5 pb-4 text-sm text-luxury-muted">
                            {{ __($item['answer'] ?? '') }}
                        </div>
                    </div>
                @endforeach
            </div>

            <p x-show="matchingCount() === 0" x-cloak class="mt-6 text-center text-sm text-luxury-muted">
                {{ __('No questions match your search.') }}
            </p>
        </div>
    </section>

    @php $faqSchema = app(\App\Services\SchemaBuilder::class)->faqPage($section->faq_items); @endphp
    @if ($faqSchema)
        <script type="application/ld+json">{!! json_encode($faqSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @endif
@endif

<script>
    function faqAccordion(index) {
        return {
            open: null,
            activeCategory: 'all',
            search: '',
            index,

            toggle(i) {
                this.open = this.open === i ? null : i;
            },

            matches(i) {
                const entry = this.index.find((v) => v.index === i);
                if (!entry) return false;

                const categoryOk = this.activeCategory === 'all' || entry.category === this.activeCategory;
                const searchOk = !this.search || entry.haystack.includes(this.search.toLowerCase());

                return categoryOk && searchOk;
            },

            matchingCount() {
                return this.index.filter((entry) => this.matches(entry.index)).length;
            },
        };
    }
</script>
