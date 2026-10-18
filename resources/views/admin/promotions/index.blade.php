<x-admin.layouts.app :title="'Promotions'">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-luxury-white">Promotions</h2>
            <p class="mt-1 text-sm text-luxury-muted">Promo banners shown on the customer dashboard.</p>
        </div>

        @permission('promotions.create')
            <a href="{{ route('admin.promotions.create') }}">
                <x-admin.button type="button" variant="primary">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Add Promotion
                </x-admin.button>
            </a>
        @endpermission
    </div>

    {{-- Desktop: table --}}
    <div class="hidden overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal sm:block">
        <div class="overflow-x-auto">
            <table class="w-full text-start text-sm">
                <thead>
                    <tr class="border-b border-luxury-border text-xs uppercase tracking-wider text-luxury-muted">
                        <th class="px-6 py-3 font-medium">Promotion</th>
                        <th class="px-6 py-3 font-medium">Window</th>
                        <th class="px-6 py-3 font-medium">Order</th>
                        <th class="px-6 py-3 font-medium">Status</th>
                        <th class="px-6 py-3 text-end font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-luxury-border/60">
                    @forelse ($promotions as $promotion)
                        <tr class="hover:bg-luxury-graphite">
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-14 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-luxury-border bg-luxury-graphite">
                                        @if ($promotion->image_url)
                                            <img src="{{ $promotion->image_url }}" alt="{{ $promotion->title }}" class="h-full w-full object-cover">
                                        @else
                                            <span class="text-[10px] text-luxury-muted">No image</span>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="font-medium text-luxury-white">{{ $promotion->title }}</p>
                                        <p class="text-xs text-luxury-muted">{{ \Illuminate\Support\Str::limit($promotion->subtitle, 40) }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-3 text-luxury-muted">
                                {{ $promotion->starts_at?->format('M d, Y') ?? 'Always' }} &ndash; {{ $promotion->ends_at?->format('M d, Y') ?? 'No end' }}
                            </td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $promotion->sort_order }}</td>
                            <td class="px-6 py-3">
                                @if ($promotion->is_active)
                                    <span class="rounded-full bg-emerald-500/10 px-2.5 py-1 text-xs font-medium text-emerald-400">Active</span>
                                @else
                                    <span class="rounded-full bg-luxury-slate px-2.5 py-1 text-xs font-medium text-luxury-muted">Inactive</span>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    @permission('promotions.edit')
                                        <a href="{{ route('admin.promotions.edit', $promotion) }}" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                            Edit
                                        </a>
                                    @endpermission
                                    @permission('promotions.delete')
                                        <form method="POST" action="{{ route('admin.promotions.destroy', $promotion) }}" onsubmit="return confirm('Delete this promotion?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg border border-red-500/30 px-3 py-1.5 text-xs font-medium text-red-400 transition hover:bg-red-500/10">
                                                Delete
                                            </button>
                                        </form>
                                    @endpermission
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-luxury-muted">No promotions created yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Mobile: cards --}}
    <div class="space-y-3 sm:hidden">
        @forelse ($promotions as $promotion)
            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-4">
                <div class="flex items-start gap-3">
                    <div class="flex h-12 w-16 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-luxury-border bg-luxury-graphite">
                        @if ($promotion->image_url)
                            <img src="{{ $promotion->image_url }}" alt="{{ $promotion->title }}" class="h-full w-full object-cover">
                        @else
                            <span class="text-[10px] text-luxury-muted">No image</span>
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-luxury-white">{{ $promotion->title }}</p>
                        <p class="truncate text-xs text-luxury-muted">{{ \Illuminate\Support\Str::limit($promotion->subtitle, 40) }}</p>
                    </div>
                    @if ($promotion->is_active)
                        <span class="shrink-0 rounded-full bg-emerald-500/10 px-2.5 py-1 text-xs font-medium text-emerald-400">Active</span>
                    @else
                        <span class="shrink-0 rounded-full bg-luxury-slate px-2.5 py-1 text-xs font-medium text-luxury-muted">Inactive</span>
                    @endif
                </div>

                <p class="mt-3 border-t border-luxury-border pt-3 text-xs text-luxury-muted">
                    {{ $promotion->starts_at?->format('M d, Y') ?? 'Always' }} &ndash; {{ $promotion->ends_at?->format('M d, Y') ?? 'No end' }}
                    &middot; {{ __('Order') }}: {{ $promotion->sort_order }}
                </p>

                <div class="mt-3 flex flex-wrap items-center gap-2 border-t border-luxury-border pt-3">
                    @permission('promotions.edit')
                        <a href="{{ route('admin.promotions.edit', $promotion) }}" class="tap-scale rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                            Edit
                        </a>
                    @endpermission
                    @permission('promotions.delete')
                        <form method="POST" action="{{ route('admin.promotions.destroy', $promotion) }}" onsubmit="return confirm('Delete this promotion?');" class="ms-auto">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="tap-scale rounded-lg border border-red-500/30 px-3 py-1.5 text-xs font-medium text-red-400 transition hover:bg-red-500/10">
                                Delete
                            </button>
                        </form>
                    @endpermission
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-10 text-center text-sm text-luxury-muted">
                No promotions created yet.
            </div>
        @endforelse
    </div>

    @if ($promotions->hasPages())
        <div class="mt-6 flex items-center justify-between text-sm text-luxury-muted">
            <div>
                @if ($promotions->onFirstPage())
                    <span class="cursor-not-allowed rounded-lg border border-luxury-border px-3 py-1.5 opacity-40">Previous</span>
                @else
                    <a href="{{ $promotions->previousPageUrl() }}" class="rounded-lg border border-luxury-border px-3 py-1.5 hover:border-luxury-gold/40 hover:text-luxury-gold">Previous</a>
                @endif
            </div>
            <p>Page {{ $promotions->currentPage() }} of {{ $promotions->lastPage() }}</p>
            <div>
                @if ($promotions->hasMorePages())
                    <a href="{{ $promotions->nextPageUrl() }}" class="rounded-lg border border-luxury-border px-3 py-1.5 hover:border-luxury-gold/40 hover:text-luxury-gold">Next</a>
                @else
                    <span class="cursor-not-allowed rounded-lg border border-luxury-border px-3 py-1.5 opacity-40">Next</span>
                @endif
            </div>
        </div>
    @endif
</x-admin.layouts.app>
