<x-customer.layouts.app :title="__('Favorites')">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Favorites') }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ __('Vehicles you\'ve saved for quick booking.') }}</p>
    </div>

    @if ($vehicles->isNotEmpty())
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($vehicles as $vehicle)
                <x-vehicle-card :vehicle="$vehicle" />
            @endforeach
        </div>

        @if ($vehicles->hasPages())
            <div class="mt-6 flex items-center justify-between text-sm text-luxury-muted">
                <div>
                    @if ($vehicles->onFirstPage())
                        <span class="cursor-not-allowed rounded-lg border border-luxury-border px-3 py-1.5 opacity-40">{{ __('Previous') }}</span>
                    @else
                        <a href="{{ $vehicles->previousPageUrl() }}" class="rounded-lg border border-luxury-border px-3 py-1.5 hover:border-luxury-gold/40 hover:text-luxury-gold">{{ __('Previous') }}</a>
                    @endif
                </div>
                <p>{{ __('Page :current of :last', ['current' => $vehicles->currentPage(), 'last' => $vehicles->lastPage()]) }}</p>
                <div>
                    @if ($vehicles->hasMorePages())
                        <a href="{{ $vehicles->nextPageUrl() }}" class="rounded-lg border border-luxury-border px-3 py-1.5 hover:border-luxury-gold/40 hover:text-luxury-gold">{{ __('Next') }}</a>
                    @else
                        <span class="cursor-not-allowed rounded-lg border border-luxury-border px-3 py-1.5 opacity-40">{{ __('Next') }}</span>
                    @endif
                </div>
            </div>
        @endif
    @else
        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-12 text-center">
            <x-icon name="heart" class="mx-auto h-10 w-10 text-luxury-muted" />
            <p class="mt-4 text-sm text-luxury-muted">{{ __("You haven't favorited any vehicles yet.") }}</p>
            <a href="{{ route('pages.show', 'services') }}" class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-luxury-gold hover:text-luxury-gold-light">
                {{ __('Browse our fleet') }}
                <x-icon name="chevron-right" class="h-4 w-4 rtl:rotate-180" />
            </a>
        </div>
    @endif
</x-customer.layouts.app>
