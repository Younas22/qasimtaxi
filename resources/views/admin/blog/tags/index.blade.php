<x-admin.layouts.app :title="__('Tags')">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Tags') }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ __('Manage tags used across blog posts.') }}</p>
    </div>

    @permission('blog.create')
        <form method="POST" action="{{ route('admin.blog.tags.store') }}" class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start">
            @csrf
            <div class="flex-1">
                <x-admin.text-input name="name" type="text" placeholder="{{ __('New tag name') }}" required class="sm:max-w-xs" />
                <x-admin.input-error :messages="$errors->get('name')" />
            </div>
            <x-admin.button type="submit" variant="primary">{{ __('Add Tag') }}</x-admin.button>
        </form>
    @endpermission

    <div class="overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal">
        <div class="overflow-x-auto">
            <table class="w-full text-start text-sm">
                <thead>
                    <tr class="border-b border-luxury-border text-xs uppercase tracking-wider text-luxury-muted">
                        <th class="px-6 py-3 font-medium">{{ __('Name') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Slug') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Posts') }}</th>
                        <th class="px-6 py-3 text-end font-medium">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-luxury-border/60">
                    @forelse ($tags as $tag)
                        <tr class="hover:bg-luxury-graphite" x-data="{ editing: false }">
                            <td class="px-6 py-3">
                                <div x-show="!editing">{{ $tag->name }}</div>
                                <form x-show="editing" x-cloak method="POST" action="{{ route('admin.blog.tags.update', $tag) }}" class="flex items-center gap-2">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="name" value="{{ $tag->name }}" class="rounded-lg border border-luxury-border bg-luxury-charcoal px-2 py-1 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                                    <button type="submit" class="text-xs text-luxury-gold hover:text-luxury-gold-light">{{ __('Save') }}</button>
                                    <button type="button" @click="editing = false" class="text-xs text-luxury-muted hover:text-luxury-white">{{ __('Cancel') }}</button>
                                </form>
                            </td>
                            <td class="px-6 py-3 font-mono text-xs text-luxury-muted">{{ $tag->slug }}</td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $tag->posts_count }}</td>
                            <td class="px-6 py-3">
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    @permission('blog.edit')
                                        <button type="button" @click="editing = !editing" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                            {{ __('Rename') }}
                                        </button>
                                    @endpermission
                                    @permission('blog.delete')
                                        <form method="POST" action="{{ route('admin.blog.tags.destroy', $tag) }}" onsubmit="return confirm('{{ __('Delete this tag?') }}');">
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
                            <td colspan="4" class="px-6 py-10 text-center text-luxury-muted">{{ __('No tags yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin.layouts.app>
