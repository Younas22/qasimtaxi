<x-admin.layouts.app :title="__('Environment (.env)')">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Environment (.env)') }}</h2>
            <p class="mt-1 text-sm text-luxury-muted">{{ __('Direct access to the application\'s .env file — app key, database, mail, and API credentials all live here.') }}</p>
        </div>
        <a href="{{ route('admin.system-tools.index') }}" class="text-sm text-luxury-muted hover:text-luxury-white">&larr; {{ __('Back to System Tools') }}</a>
    </div>

    <div class="mb-6 flex items-start gap-3 rounded-xl border border-red-500/30 bg-red-500/5 p-4">
        <x-icon name="shield" class="mt-0.5 h-5 w-5 shrink-0 text-red-400" />
        <p class="text-sm text-red-300">
            {{ __('A wrong value here (a bad database password, a missing APP_KEY) can take the entire site down immediately for every visitor. A backup of the current file is made automatically every time you save or restore — use it below if something goes wrong.') }}
        </p>
    </div>

    <form method="POST" action="{{ route('admin.system-tools.env.update') }}" class="space-y-4">
        @csrf
        @method('PUT')

        <textarea name="content" spellcheck="false"
            class="h-[32rem] w-full rounded-xl border border-luxury-border bg-luxury-black p-4 font-mono text-xs leading-relaxed text-luxury-white focus:border-luxury-gold/50 focus:outline-none focus:ring-1 focus:ring-luxury-gold/50">{{ old('content', $content) }}</textarea>
        <x-admin.input-error :messages="$errors->get('content')" />

        <div class="flex items-center justify-end gap-3">
            <x-admin.button type="submit" variant="primary">{{ __('Save .env File') }}</x-admin.button>
        </div>
    </form>

    <div class="mt-8">
        <h3 class="mb-3 text-sm font-semibold text-luxury-white">{{ __('Backups') }}</h3>

        @if (empty($backups))
            <p class="text-sm text-luxury-muted">{{ __('No backups yet — one is created automatically the first time you save.') }}</p>
        @else
            <div class="overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal">
                <div class="divide-y divide-luxury-border/60">
                    @foreach ($backups as $backup)
                        <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-3.5">
                            <div class="min-w-0">
                                <p class="truncate text-sm text-luxury-white">{{ $backup['created_at']->format('M d, Y — h:i:s A') }}</p>
                                <p class="text-xs text-luxury-muted">{{ $backup['filename'] }} &middot; {{ $backup['size_kb'] }} KB</p>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                <form method="POST" action="{{ route('admin.system-tools.env.backups.restore', $backup['filename']) }}"
                                    onsubmit="return confirm('{{ __('Overwrite the current .env with this backup? The current file will itself be backed up first.') }}');">
                                    @csrf
                                    <button type="submit" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                        {{ __('Restore') }}
                                    </button>
                                </form>
                                @permission('system.delete')
                                    <form method="POST" action="{{ route('admin.system-tools.env.backups.destroy', $backup['filename']) }}"
                                        onsubmit="return confirm('{{ __('Delete this backup permanently?') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-red-400/40 hover:text-red-400">
                                            {{ __('Delete') }}
                                        </button>
                                    </form>
                                @endpermission
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-admin.layouts.app>
