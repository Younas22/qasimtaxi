<x-admin.layouts.app :title="__('Reviews')">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Reviews') }}</h2>
            <p class="mt-1 text-sm text-luxury-muted">{{ __('Moderate customer, driver, and vehicle reviews before they appear as testimonials.') }}</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.customers.index') }}" class="rounded-lg border border-luxury-border px-4 py-2.5 text-sm font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                {{ __('Customers') }}
            </a>
            @permission('reviews.create')
                <a href="{{ route('admin.reviews.create') }}">
                    <x-admin.button type="button" variant="primary">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        {{ __('Add Review') }}
                    </x-admin.button>
                </a>
            @endpermission
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="mb-6 flex flex-wrap items-center gap-3">
        <select name="status" onchange="this.form.submit()"
            class="rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
            <option value="">{{ __('All Statuses') }}</option>
            @foreach (\App\Models\Review::STATUSES as $value => $label)
                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
            @endforeach
        </select>

        <select name="type" onchange="this.form.submit()"
            class="rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
            <option value="">{{ __('All Reviews') }}</option>
            <option value="driver" @selected(request('type') === 'driver')>{{ __('Driver Reviews') }}</option>
            <option value="vehicle" @selected(request('type') === 'vehicle')>{{ __('Vehicle Reviews') }}</option>
        </select>

        <select name="rating" onchange="this.form.submit()"
            class="rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
            <option value="">{{ __('All Ratings') }}</option>
            @for ($i = 5; $i >= 1; $i--)
                <option value="{{ $i }}" @selected((string) request('rating') === (string) $i)>{{ $i }} {{ $i > 1 ? __('Stars') : __('Star') }}</option>
            @endfor
        </select>

        @if (request()->filled('status') || request()->filled('type') || request()->filled('rating'))
            <a href="{{ route('admin.reviews.index') }}" class="text-xs text-luxury-muted hover:text-luxury-gold">{{ __('Clear filters') }}</a>
        @endif
    </form>

    <div class="overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal">
        <div class="overflow-x-auto">
            <table class="w-full text-start text-sm">
                <thead>
                    <tr class="border-b border-luxury-border text-xs uppercase tracking-wider text-luxury-muted">
                        <th class="px-6 py-3 font-medium">{{ __('Customer') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Rating') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Comment') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Driver / Vehicle') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Status') }}</th>
                        <th class="px-6 py-3 text-end font-medium">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-luxury-border/60">
                    @forelse ($reviews as $review)
                        <tr class="hover:bg-luxury-graphite">
                            <td class="px-6 py-3">
                                <a href="{{ route('admin.customers.show', $review->customer) }}" class="font-medium text-luxury-white hover:text-luxury-gold">
                                    {{ $review->customer->name }}
                                </a>
                            </td>
                            <td class="px-6 py-3">
                                <x-rating-stars :rating="$review->rating" size="h-3.5 w-3.5" />
                            </td>
                            <td class="max-w-xs px-6 py-3 text-luxury-muted">
                                <p class="truncate">{{ $review->comment ?? '—' }}</p>
                            </td>
                            <td class="px-6 py-3 text-luxury-muted">
                                <p class="text-xs">{{ $review->driver?->name ?? '—' }}</p>
                                <p class="text-xs">{{ $review->vehicle?->name ?? '—' }}</p>
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $review->status === 'approved' ? 'bg-emerald-500/10 text-emerald-400' : ($review->status === 'pending' ? 'bg-luxury-gold/10 text-luxury-gold' : 'bg-red-500/10 text-red-400') }}">
                                        {{ $review->status_label }}
                                    </span>
                                    @if ($review->is_featured)
                                        <span class="rounded-full bg-luxury-gold/10 px-2.5 py-1 text-xs font-medium text-luxury-gold">{{ __('Featured') }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    @permission('reviews.edit')
                                        <a href="{{ route('admin.reviews.edit', $review) }}" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                            {{ __('Edit') }}
                                        </a>
                                        @if ($review->status !== 'approved')
                                            <form method="POST" action="{{ route('admin.reviews.approve', $review) }}">
                                                @csrf
                                                <button type="submit" class="rounded-lg border border-emerald-500/30 px-3 py-1.5 text-xs font-medium text-emerald-400 transition hover:bg-emerald-500/10">
                                                    {{ __('Approve') }}
                                                </button>
                                            </form>
                                        @endif
                                        @if ($review->status !== 'rejected')
                                            <form method="POST" action="{{ route('admin.reviews.reject', $review) }}">
                                                @csrf
                                                <button type="submit" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-red-500/40 hover:text-red-400">
                                                    {{ __('Reject') }}
                                                </button>
                                            </form>
                                        @endif
                                        @if ($review->status === 'approved')
                                            <form method="POST" action="{{ route('admin.reviews.toggle-featured', $review) }}">
                                                @csrf
                                                <button type="submit" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                                    {{ $review->is_featured ? __('Unfeature') : __('Feature') }}
                                                </button>
                                            </form>
                                        @endif
                                    @endpermission
                                    @permission('reviews.delete')
                                        <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}" onsubmit="return confirm('{{ __('Delete this review?') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg border border-red-500/30 px-3 py-1.5 text-xs font-medium text-red-400 transition hover:bg-red-500/10">
                                                {{ __('Delete') }}
                                            </button>
                                        </form>
                                    @endpermission
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-luxury-muted">{{ __('No reviews found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
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
</x-admin.layouts.app>
