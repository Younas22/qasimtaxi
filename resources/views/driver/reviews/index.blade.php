<x-driver.layouts.app :title="__('Reviews')">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Reviews') }}</h2>
            <p class="mt-1 text-sm text-luxury-muted">{{ __('What customers are saying about you.') }}</p>
        </div>
        @if ($averageRating)
            <div class="flex items-center gap-2 rounded-2xl border border-luxury-border bg-luxury-charcoal px-5 py-3">
                <x-rating-stars :rating="$averageRating" size="h-4 w-4" />
                <span class="text-lg font-semibold text-luxury-white">{{ $averageRating }}</span>
            </div>
        @endif
    </div>

    <div class="space-y-4">
        @forelse ($reviews as $review)
            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-luxury-white">{{ $review->customer?->name ?? __('Customer') }}</p>
                        <p class="text-xs text-luxury-muted">{{ $review->booking?->booking_number }} · {{ $review->created_at->format('M d, Y') }}</p>
                    </div>
                    <x-rating-stars :rating="$review->rating" size="h-4 w-4" />
                </div>
                @if ($review->comment)
                    <p class="mt-3 text-sm text-luxury-muted">{{ $review->comment }}</p>
                @endif
            </div>
        @empty
            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-8 text-center">
                <x-icon name="star" class="mx-auto h-8 w-8 text-luxury-muted" />
                <p class="mt-3 text-sm text-luxury-muted">{{ __('No reviews yet.') }}</p>
            </div>
        @endforelse
    </div>

    @if ($reviews->hasPages())
        <div class="mt-6 flex items-center justify-between text-sm text-luxury-muted">
            <div>
                @if ($reviews->onFirstPage())
                    <span class="cursor-not-allowed rounded-lg border border-luxury-border px-3 py-1.5 opacity-40">{{ __('Previous') }}</span>
                @else
                    <a href="{{ $reviews->previousPageUrl() }}" class="rounded-lg border border-luxury-border px-3 py-1.5 hover:border-luxury-gold/40 hover:text-luxury-gold">{{ __('Previous') }}</a>
                @endif
            </div>
            <p>{{ __('Page :current of :last', ['current' => $reviews->currentPage(), 'last' => $reviews->lastPage()]) }}</p>
            <div>
                @if ($reviews->hasMorePages())
                    <a href="{{ $reviews->nextPageUrl() }}" class="rounded-lg border border-luxury-border px-3 py-1.5 hover:border-luxury-gold/40 hover:text-luxury-gold">{{ __('Next') }}</a>
                @else
                    <span class="cursor-not-allowed rounded-lg border border-luxury-border px-3 py-1.5 opacity-40">{{ __('Next') }}</span>
                @endif
            </div>
        </div>
    @endif
</x-driver.layouts.app>
