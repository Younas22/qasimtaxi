<x-customer.layouts.app :title="__('Reviews')">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('My Reviews') }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ __("Reviews you've left for drivers and vehicles.") }}</p>
    </div>

    <div class="space-y-4">
        @forelse ($reviews as $review)
            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <x-rating-stars :rating="$review->rating" size="h-4 w-4" />
                    <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $review->status === 'approved' ? 'bg-emerald-500/10 text-emerald-400' : ($review->status === 'pending' ? 'bg-luxury-gold/10 text-luxury-gold' : 'bg-red-500/10 text-red-400') }}">
                        {{ $review->status_label }}
                    </span>
                </div>
                @if ($review->comment)
                    <p class="mt-3 text-sm text-luxury-muted">&ldquo;{{ $review->comment }}&rdquo;</p>
                @endif
                <div class="mt-4 flex flex-wrap items-center gap-3 border-t border-luxury-border pt-4 text-xs text-luxury-muted">
                    @if ($review->driver)
                        <span>{{ __('Driver') }}: {{ $review->driver->name }}</span>
                    @endif
                    @if ($review->vehicle)
                        <span>{{ __('Vehicle') }}: {{ $review->vehicle->name }}</span>
                    @endif
                    <span class="ms-auto">{{ $review->created_at->format('M d, Y') }}</span>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-12 text-center">
                <x-icon name="star" class="mx-auto h-10 w-10 text-luxury-muted" />
                <p class="mt-4 text-sm text-luxury-muted">{{ __("You haven't left any reviews yet.") }}</p>
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
</x-customer.layouts.app>
