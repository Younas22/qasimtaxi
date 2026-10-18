<x-admin.layouts.app :title="__('Blog Posts')">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Blog Posts') }}</h2>
            <p class="mt-1 text-sm text-luxury-muted">{{ __('Write, publish, and manage your blog.') }}</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.blog.tags.index') }}" class="rounded-lg border border-luxury-border px-4 py-2.5 text-sm font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                {{ __('Tags') }}
            </a>
            <a href="{{ route('admin.blog.categories.index') }}" class="rounded-lg border border-luxury-border px-4 py-2.5 text-sm font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                {{ __('Categories') }}
            </a>
            @permission('blog.create')
                <a href="{{ route('admin.blog.create') }}">
                    <x-admin.button type="button" variant="primary">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        {{ __('New Post') }}
                    </x-admin.button>
                </a>
            @endpermission
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="mb-6 flex flex-wrap items-center gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search title...') }}"
            class="rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">

        <select name="status" onchange="this.form.submit()"
            class="rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
            <option value="">{{ __('All Statuses') }}</option>
            @foreach (\App\Models\BlogPost::STATUSES as $value => $label)
                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
            @endforeach
        </select>

        <select name="category" onchange="this.form.submit()"
            class="rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
            <option value="">{{ __('All Categories') }}</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>

        <button type="submit" class="rounded-lg border border-luxury-border px-3 py-2 text-sm text-luxury-muted hover:border-luxury-gold/40 hover:text-luxury-gold">{{ __('Filter') }}</button>

        @if (request()->filled('status') || request()->filled('category') || request()->filled('search'))
            <a href="{{ route('admin.blog.index') }}" class="text-xs text-luxury-muted hover:text-luxury-gold">{{ __('Clear filters') }}</a>
        @endif
    </form>

    <div class="overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal">
        <div class="overflow-x-auto">
            <table class="w-full text-start text-sm">
                <thead>
                    <tr class="border-b border-luxury-border text-xs uppercase tracking-wider text-luxury-muted">
                        <th class="px-6 py-3 font-medium">{{ __('Post') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Category') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Author') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Status') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Views') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Featured') }}</th>
                        <th class="px-6 py-3 text-end font-medium">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-luxury-border/60">
                    @forelse ($posts as $post)
                        <tr class="hover:bg-luxury-graphite">
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-14 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-luxury-border bg-luxury-graphite">
                                        @if ($post->featured_image_url)
                                            <img src="{{ $post->featured_image_url }}" alt="" class="h-full w-full object-cover">
                                        @else
                                            <span class="text-[9px] text-luxury-muted">{{ __('No image') }}</span>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate font-medium text-luxury-white">{{ $post->title }}</p>
                                        <p class="text-xs text-luxury-muted">/{{ $post->slug }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $post->category?->name ?? '—' }}</td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $post->author?->name ?? '—' }}</td>
                            <td class="px-6 py-3">
                                <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $post->status === 'published' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-luxury-slate text-luxury-muted' }}">
                                    {{ $post->status_label }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-luxury-muted">{{ number_format($post->views_count) }}</td>
                            <td class="px-6 py-3">
                                @permission('blog.edit')
                                    <form method="POST" action="{{ route('admin.blog.toggle-featured', $post) }}">
                                        @csrf
                                        <button type="submit" class="rounded-full p-1.5 transition {{ $post->is_featured ? 'text-luxury-gold' : 'text-luxury-muted hover:text-luxury-gold' }}" title="{{ __('Toggle featured') }}">
                                            <svg class="h-5 w-5" fill="{{ $post->is_featured ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.98 21.539a.562.562 0 01-.84-.61l1.285-5.385a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                                            </svg>
                                        </button>
                                    </form>
                                @else
                                    @if ($post->is_featured)
                                        <span class="text-luxury-gold">{{ __('Featured') }}</span>
                                    @endif
                                @endpermission
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    @permission('blog.edit')
                                        <a href="{{ route('admin.blog.edit', $post) }}" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                            {{ __('Edit') }}
                                        </a>
                                    @endpermission
                                    @permission('blog.delete')
                                        <form method="POST" action="{{ route('admin.blog.destroy', $post) }}" onsubmit="return confirm('{{ __('Delete this post?') }}');">
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
                            <td colspan="7" class="px-6 py-10 text-center text-luxury-muted">{{ __('No blog posts found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($posts->hasPages())
        <div class="mt-6 flex items-center justify-between text-sm text-luxury-muted">
            <div>
                @if ($posts->onFirstPage())
                    <span class="cursor-not-allowed rounded-lg border border-luxury-border px-3 py-1.5 opacity-40">{{ __('Previous') }}</span>
                @else
                    <a href="{{ $posts->previousPageUrl() }}" class="rounded-lg border border-luxury-border px-3 py-1.5 hover:border-luxury-gold/40 hover:text-luxury-gold">{{ __('Previous') }}</a>
                @endif
            </div>
            <p>{{ __('Page :current of :last', ['current' => $posts->currentPage(), 'last' => $posts->lastPage()]) }}</p>
            <div>
                @if ($posts->hasMorePages())
                    <a href="{{ $posts->nextPageUrl() }}" class="rounded-lg border border-luxury-border px-3 py-1.5 hover:border-luxury-gold/40 hover:text-luxury-gold">{{ __('Next') }}</a>
                @else
                    <span class="cursor-not-allowed rounded-lg border border-luxury-border px-3 py-1.5 opacity-40">{{ __('Next') }}</span>
                @endif
            </div>
        </div>
    @endif
</x-admin.layouts.app>
