<x-admin.layouts.app :title="__('Redirects')">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Redirects') }}</h2>
            <p class="mt-1 text-sm text-luxury-muted">{{ __('Send visitors (and search engines) from an old URL to its new location instead of a broken link.') }}</p>
        </div>

        @permission('content.create')
            <a href="{{ route('admin.redirects.create') }}">
                <x-admin.button type="button" variant="primary">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    {{ __('Add Redirect') }}
                </x-admin.button>
            </a>
        @endpermission
    </div>

    <div class="overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal">
        <div class="overflow-x-auto">
            <table class="w-full text-start text-sm">
                <thead>
                    <tr class="border-b border-luxury-border text-xs uppercase tracking-wider text-luxury-muted">
                        <th class="px-6 py-3 font-medium">{{ __('Old Path') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('New Path') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Type') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Status') }}</th>
                        <th class="px-6 py-3 text-end font-medium">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-luxury-border/60">
                    @forelse ($redirects as $redirect)
                        <tr class="hover:bg-luxury-graphite">
                            <td class="px-6 py-3 font-mono text-xs text-luxury-white">/{{ $redirect->old_path }}</td>
                            <td class="px-6 py-3 font-mono text-xs text-luxury-muted">/{{ $redirect->new_path }}</td>
                            <td class="px-6 py-3 text-luxury-muted">{{ \App\Models\Redirect::TYPES[$redirect->type] ?? $redirect->type }}</td>
                            <td class="px-6 py-3"><x-admin.status-badge :active="$redirect->is_active" /></td>
                            <td class="px-6 py-3">
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    @permission('content.edit')
                                        <a href="{{ route('admin.redirects.edit', $redirect) }}" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                            {{ __('Edit') }}
                                        </a>
                                    @endpermission
                                    @permission('content.delete')
                                        <form method="POST" action="{{ route('admin.redirects.destroy', $redirect) }}" onsubmit="return confirm('{{ __('Delete this redirect?') }}');">
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
                            <td colspan="5" class="px-6 py-10 text-center text-luxury-muted">{{ __('No redirects added yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $redirects->links() }}
    </div>
</x-admin.layouts.app>
