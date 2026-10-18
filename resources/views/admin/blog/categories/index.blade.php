<x-admin.layouts.app :title="__('Blog Categories')">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Blog Categories') }}</h2>
            <p class="mt-1 text-sm text-luxury-muted">{{ __('Organize posts into categories.') }}</p>
        </div>

        @permission('blog.create')
            <a href="{{ route('admin.blog.categories.create') }}">
                <x-admin.button type="button" variant="primary">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    {{ __('Add Category') }}
                </x-admin.button>
            </a>
        @endpermission
    </div>

    <div class="overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal">
        <div class="overflow-x-auto">
            <table class="w-full text-start text-sm">
                <thead>
                    <tr class="border-b border-luxury-border text-xs uppercase tracking-wider text-luxury-muted">
                        <th class="px-6 py-3 font-medium">{{ __('Order') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Image') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Category') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Posts') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Status') }}</th>
                        <th class="px-6 py-3 text-end font-medium">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-luxury-border/60">
                    @forelse ($categories as $category)
                        <tr class="hover:bg-luxury-graphite">
                            <td class="px-6 py-3">
                                @permission('blog.edit')
                                    <div class="flex items-center gap-1">
                                        <form method="POST" action="{{ route('admin.blog.categories.move-up', $category) }}">
                                            @csrf
                                            <button type="submit" @if ($loop->first) disabled @endif
                                                class="flex h-7 w-7 items-center justify-center rounded-md border border-luxury-border text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold disabled:cursor-not-allowed disabled:opacity-30">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" />
                                                </svg>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.blog.categories.move-down', $category) }}">
                                            @csrf
                                            <button type="submit" @if ($loop->last) disabled @endif
                                                class="flex h-7 w-7 items-center justify-center rounded-md border border-luxury-border text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold disabled:cursor-not-allowed disabled:opacity-30">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-luxury-muted">{{ $loop->iteration }}</span>
                                @endpermission
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex h-10 w-16 items-center justify-center overflow-hidden rounded-lg border border-luxury-border bg-luxury-graphite">
                                    @if ($category->image_url)
                                        <img src="{{ $category->image_url }}" alt="{{ $category->name }}" class="h-full w-full object-cover">
                                    @else
                                        <span class="text-[10px] text-luxury-muted">{{ __('No image') }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-3">
                                <p class="font-medium text-luxury-white">{{ $category->name }}</p>
                                @if ($category->description)
                                    <p class="max-w-xs truncate text-xs text-luxury-muted">{{ $category->description }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $category->posts_count }}</td>
                            <td class="px-6 py-3"><x-admin.status-badge :active="$category->is_active" /></td>
                            <td class="px-6 py-3">
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    @permission('blog.edit')
                                        <a href="{{ route('admin.blog.categories.edit', $category) }}" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                            {{ __('Edit') }}
                                        </a>
                                        <form method="POST" action="{{ route('admin.blog.categories.toggle', $category) }}">
                                            @csrf
                                            <button type="submit" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                                {{ $category->is_active ? __('Disable') : __('Enable') }}
                                            </button>
                                        </form>
                                    @endpermission

                                    @permission('blog.delete')
                                        <form method="POST" action="{{ route('admin.blog.categories.destroy', $category) }}" onsubmit="return confirm('{{ __('Delete this category?') }}');">
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
                            <td colspan="6" class="px-6 py-10 text-center text-luxury-muted">{{ __('No blog categories added yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin.layouts.app>
