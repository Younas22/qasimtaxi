<x-admin.layouts.app :title="__('Edit Driver')">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Edit Driver') }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ __('Update the details for :name.', ['name' => $driver->name]) }}</p>
    </div>

    <form method="POST" action="{{ route('admin.drivers.update', $driver) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')
        @include('admin.drivers._form', ['vehicles' => $vehicles, 'driver' => $driver])

        <div class="flex items-center gap-3">
            <x-admin.button type="submit" variant="primary">{{ __('Save Changes') }}</x-admin.button>
            <a href="{{ route('admin.drivers.index') }}" class="text-sm text-luxury-muted hover:text-luxury-white">{{ __('Cancel') }}</a>
        </div>
    </form>

    {{-- Reviews --}}
    <div class="mt-6 overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal">
        <div class="flex items-center justify-between border-b border-luxury-border px-6 py-4">
            <h3 class="text-sm font-semibold text-luxury-white">{{ __('Driver Reviews') }}</h3>
            @if ($driver->average_rating)
                <div class="flex items-center gap-2">
                    <x-rating-stars :rating="$driver->average_rating" size="h-4 w-4" />
                    <span class="text-sm font-medium text-luxury-white">{{ $driver->average_rating }}</span>
                    <span class="text-xs text-luxury-muted">({{ $driver->reviews->where('status', 'approved')->count() }})</span>
                </div>
            @endif
        </div>
        <div class="divide-y divide-luxury-border/60">
            @forelse ($driver->reviews as $review)
                <div class="px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-luxury-white">{{ $review->customer?->name ?? __('Unknown customer') }}</p>
                            <x-rating-stars :rating="$review->rating" size="h-3.5 w-3.5" class="mt-1" />
                        </div>
                        <span class="rounded-full px-2 py-0.5 text-[10px] font-medium uppercase tracking-wide {{ $review->status === 'approved' ? 'bg-emerald-500/10 text-emerald-400' : ($review->status === 'pending' ? 'bg-luxury-gold/10 text-luxury-gold' : 'bg-red-500/10 text-red-400') }}">
                            {{ $review->status_label }}
                        </span>
                    </div>
                    @if ($review->comment)
                        <p class="mt-2 text-sm text-luxury-muted">{{ $review->comment }}</p>
                    @endif
                    <p class="mt-1 text-xs text-luxury-muted">{{ $review->created_at->format('M d, Y') }}</p>
                </div>
            @empty
                <p class="px-6 py-8 text-center text-luxury-muted">{{ __('No reviews yet.') }}</p>
            @endforelse
        </div>
    </div>
</x-admin.layouts.app>
