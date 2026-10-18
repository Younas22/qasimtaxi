{{--
    Assumes it renders within a parent scope exposing Alpine state `searchOpen`
    (declared once on layouts.public). UI shell only — no search backend yet.
--}}

<div x-show="searchOpen" x-cloak
    class="fixed inset-0 z-50"
    x-trap.noscroll="searchOpen"
    @keydown.escape.window="searchOpen = false">

    <div x-show="searchOpen" x-transition.opacity @click="searchOpen = false" class="fixed inset-0 bg-black/70"></div>

    <div x-show="searchOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        class="theme-dark-scope relative mx-auto mt-0 flex min-h-screen w-full flex-col bg-luxury-black sm:mt-24 sm:min-h-0 sm:max-w-xl sm:rounded-2xl sm:border sm:border-luxury-border sm:shadow-2xl">

        <div class="flex items-center gap-3 border-b border-luxury-border px-5 py-4">
            <x-icon name="search" class="h-5 w-5 shrink-0 text-luxury-muted" />
            <input type="search" x-ref="searchInput" x-init="$watch('searchOpen', value => value && setTimeout(() => $refs.searchInput.focus(), 50))"
                placeholder="{{ __('Search destinations, services...') }}" autocomplete="off"
                class="w-full bg-transparent text-sm text-luxury-white placeholder:text-luxury-muted focus:outline-none">
            <button type="button" @click="searchOpen = false" aria-label="{{ __('Close search') }}"
                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-luxury-muted hover:bg-luxury-graphite hover:text-luxury-white">
                <x-icon name="close" class="h-4 w-4" />
            </button>
        </div>

        <div class="flex-1 overflow-y-auto px-5 py-5 sm:max-h-96">
            <p class="mb-3 text-xs font-medium uppercase tracking-wider text-luxury-muted">{{ __('Quick Links') }}</p>
            <div class="space-y-1">
                @foreach (['services' => ['icon' => 'car', 'label' => __('Our Services')], 'faq' => ['icon' => 'chat', 'label' => __('FAQ')], 'contact' => ['icon' => 'phone', 'label' => __('Contact Us')]] as $slug => $item)
                    <a href="{{ route('pages.show', $slug) }}" @click="searchOpen = false"
                        class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-luxury-muted transition hover:bg-luxury-graphite hover:text-luxury-white">
                        <x-icon :name="$item['icon']" class="h-4 w-4" />
                        {{ $item['label'] }}
                    </a>
                @endforeach
                <a href="{{ route('blog.index') }}" @click="searchOpen = false"
                    class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-luxury-muted transition hover:bg-luxury-graphite hover:text-luxury-white">
                    <x-icon name="pencil" class="h-4 w-4" />
                    {{ __('Blog') }}
                </a>
            </div>
        </div>
    </div>
</div>
