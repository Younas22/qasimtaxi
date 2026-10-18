<x-admin.layouts.app :title="__('System Tools')">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-luxury-white">{{ __('System Tools') }}</h2>
            <p class="mt-1 text-sm text-luxury-muted">{{ __('Maintenance, caching, backups, and diagnostics for this installation.') }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @permission('system.view')
                <a href="{{ route('admin.system-tools.activity-logs') }}" class="rounded-lg border border-luxury-border px-4 py-2.5 text-sm font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                    {{ __('Activity Logs') }}
                </a>
                <a href="{{ route('admin.system-tools.error-logs') }}" class="rounded-lg border border-luxury-border px-4 py-2.5 text-sm font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                    {{ __('Error Logs') }}
                </a>
                <a href="{{ route('admin.system-tools.env.edit') }}" class="rounded-lg border border-luxury-border px-4 py-2.5 text-sm font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                    {{ __('Environment (.env)') }}
                </a>
            @endpermission
        </div>
    </div>

    @if (session('command_output'))
        <div class="mb-6 rounded-xl border border-luxury-border bg-luxury-charcoal p-5">
            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-luxury-muted">{{ __('Command Output') }}</p>
            <pre class="max-h-72 overflow-auto whitespace-pre-wrap break-words rounded-lg bg-luxury-black/60 p-4 text-xs text-luxury-muted">{{ session('command_output') }}</pre>
        </div>
    @endif

    @if ($isDownForMaintenance)
        <div class="mb-6 rounded-xl border border-red-500/30 bg-red-500/10 px-5 py-4">
            <p class="text-sm font-semibold text-red-400">{{ __('Maintenance mode is currently ON — the public site is unavailable to visitors.') }}</p>
            @if ($maintenanceSecret)
                <p class="mt-1 text-xs text-red-300">{{ __('Bypass link:') }} <code class="rounded bg-luxury-charcoal px-1.5 py-0.5">{{ url('/') }}/{{ $maintenanceSecret }}</code></p>
            @endif
        </div>
    @endif

    {{-- System Health --}}
    <div class="mb-6 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
        <h3 class="mb-4 text-sm font-semibold text-luxury-white">{{ __('System Health') }}</h3>
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($checks as $check)
                <div class="flex items-start gap-3 rounded-xl border border-luxury-border/60 bg-luxury-graphite/40 p-4">
                    <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full {{ match ($check['status']) {
                        'ok' => 'bg-emerald-500/10 text-emerald-400',
                        'warning' => 'bg-luxury-gold/10 text-luxury-gold',
                        default => 'bg-red-500/10 text-red-400',
                    } }}">
                        @if ($check['status'] === 'ok')
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        @elseif ($check['status'] === 'warning')
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                        @else
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        @endif
                    </span>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-luxury-white">{{ $check['label'] }}</p>
                        <p class="truncate text-xs text-luxury-muted">{{ $check['detail'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-5 grid grid-cols-2 gap-3 border-t border-luxury-border pt-5 sm:grid-cols-5">
            @foreach ($systemInfo as $label => $value)
                <div>
                    <p class="text-[11px] uppercase tracking-wide text-luxury-muted">{{ $label }}</p>
                    <p class="mt-1 text-sm font-medium text-luxury-white">{{ $value }}</p>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Maintenance Mode --}}
        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
            <h3 class="text-sm font-semibold text-luxury-white">{{ __('Maintenance Mode') }}</h3>
            <p class="mt-1 text-xs text-luxury-muted">{{ __('Take the public site offline while you make changes. The admin panel stays accessible.') }}</p>

            <form method="POST" action="{{ route('admin.system-tools.maintenance.toggle') }}" class="mt-4">
                @csrf
                @permission('system.edit')
                    <x-admin.button type="submit" :variant="$isDownForMaintenance ? 'primary' : 'danger'">
                        {{ $isDownForMaintenance ? __('Disable Maintenance Mode') : __('Enable Maintenance Mode') }}
                    </x-admin.button>
                @endpermission
            </form>
        </div>

        {{-- Cache --}}
        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
            <h3 class="text-sm font-semibold text-luxury-white">{{ __('Cache') }}</h3>
            <p class="mt-1 text-xs text-luxury-muted">{{ __('Clear cached config, routes, views, or application data.') }}</p>

            @permission('system.edit')
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach (['all' => __('Clear All'), 'config' => __('Config'), 'route' => __('Routes'), 'view' => __('Views'), 'application' => __('App Cache')] as $target => $label)
                        <form method="POST" action="{{ route('admin.system-tools.cache.clear') }}">
                            @csrf
                            <input type="hidden" name="target" value="{{ $target }}">
                            <button type="submit" class="rounded-lg border border-luxury-border px-3 py-2 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                {{ $label }}
                            </button>
                        </form>
                    @endforeach
                </div>
            @endpermission
        </div>

        {{-- Queue Status --}}
        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
            <h3 class="text-sm font-semibold text-luxury-white">{{ __('Queue Status') }}</h3>
            <p class="mt-1 text-xs text-luxury-muted">{{ __('Driver:') }} <span class="font-medium text-luxury-white">{{ ucfirst($queue['driver']) }}</span></p>

            @if ($queue['pending'] === null)
                <p class="mt-4 text-sm text-luxury-muted">{{ __('This driver processes jobs immediately — there is no queue to monitor.') }}</p>
            @else
                <div class="mt-4 grid grid-cols-2 gap-3">
                    <div class="rounded-xl border border-luxury-border/60 bg-luxury-graphite/40 p-4 text-center">
                        <p class="text-2xl font-semibold text-luxury-white">{{ number_format($queue['pending']) }}</p>
                        <p class="text-xs text-luxury-muted">{{ __('Pending Jobs') }}</p>
                    </div>
                    <div class="rounded-xl border border-luxury-border/60 bg-luxury-graphite/40 p-4 text-center">
                        <p class="text-2xl font-semibold {{ $queue['failed'] > 0 ? 'text-red-400' : 'text-luxury-white' }}">{{ number_format($queue['failed']) }}</p>
                        <p class="text-xs text-luxury-muted">{{ __('Failed Jobs') }}</p>
                    </div>
                </div>

                @if ($queue['failed'] > 0)
                    @permission('system.edit')
                        <form method="POST" action="{{ route('admin.system-tools.queue.failed.clear') }}" class="mt-3" onsubmit="return confirm('{{ __('Clear all failed jobs?') }}');">
                            @csrf
                            <button type="submit" class="w-full rounded-lg border border-red-500/30 px-3 py-2 text-xs font-medium text-red-400 transition hover:bg-red-500/10">
                                {{ __('Clear Failed Jobs') }}
                            </button>
                        </form>
                    @endpermission
                @endif
            @endif
        </div>

        {{-- Backups --}}
        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6 lg:col-span-2">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h3 class="text-sm font-semibold text-luxury-white">{{ __('Database Backups') }}</h3>
                @permission('system.edit')
                    <div class="flex flex-wrap items-start gap-2">
                        <div x-data="backupUploader('{{ route('admin.system-tools.backups.upload') }}')">
                            <label class="flex items-center gap-2 rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold"
                                :class="uploading ? 'cursor-not-allowed opacity-60' : 'cursor-pointer'">
                                <svg x-show="uploading" x-cloak class="h-3.5 w-3.5 animate-spin text-luxury-gold" viewBox="0 0 24 24" fill="none">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <span x-show="!uploading" x-text="filename || '{{ __('Upload .sql File') }}'"></span>
                                <span x-show="uploading" x-text="'{{ __('Uploading') }}… ' + progress + '%'"></span>
                                <input type="file" name="file" accept=".sql" class="hidden" :disabled="uploading"
                                    @change="upload($event.target.files[0]); $event.target.value = ''">
                            </label>
                            <div x-show="uploading" x-cloak class="mt-1.5 h-1 w-40 overflow-hidden rounded-full bg-luxury-graphite">
                                <div class="h-full bg-luxury-gold transition-all" :style="`width: ${progress}%`"></div>
                            </div>
                            <p x-show="error" x-cloak x-text="error" class="mt-1.5 max-w-xs text-[11px] text-red-400"></p>
                        </div>
                        <form method="POST" action="{{ route('admin.system-tools.backups.create') }}">
                            @csrf
                            <button type="submit" class="rounded-lg bg-luxury-gold px-3 py-1.5 text-xs font-semibold text-luxury-black transition hover:bg-luxury-gold-light">
                                + {{ __('New Backup') }}
                            </button>
                        </form>
                    </div>
                @endpermission
            </div>
            <x-admin.input-error :messages="$errors->get('file')" class="mt-2" />

            <div class="mt-4 max-h-64 space-y-2 overflow-y-auto">
                @forelse ($backups as $backup)
                    <div class="flex flex-wrap items-center justify-between gap-2 rounded-lg border border-luxury-border/60 bg-luxury-graphite/40 px-4 py-2.5">
                        <div class="min-w-0">
                            <p class="truncate text-xs font-medium text-luxury-white">{{ $backup['name'] }}</p>
                            <p class="text-[11px] text-luxury-muted">{{ $backup['size'] }} &middot; {{ $backup['created_at']->format('M d, Y H:i') }}</p>
                        </div>
                        <div class="flex shrink-0 items-center gap-1">
                            @permission('system.export')
                                <a href="{{ route('admin.system-tools.backups.download', $backup['name']) }}" class="rounded-lg border border-luxury-border px-2.5 py-1.5 text-[11px] font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                    {{ __('Download') }}
                                </a>
                            @endpermission
                            @permission('system.delete')
                                <div x-data="backupRestorer()" class="inline-block">
                                    <button type="button" @click="open = true" class="rounded-lg border border-luxury-gold/40 px-2.5 py-1.5 text-[11px] font-medium text-luxury-gold transition hover:bg-luxury-gold/10">
                                        {{ __('Update DB') }}
                                    </button>

                                    <div x-show="open" x-cloak x-transition.opacity
                                        class="fixed inset-0 z-[70] flex items-end justify-center bg-black/70 sm:items-center sm:p-4"
                                        @keydown.escape.window="open = false">
                                        <div @click.outside="open = false" x-show="open"
                                            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                                            class="w-full max-w-sm rounded-t-2xl border border-luxury-border bg-luxury-charcoal p-6 text-start sm:rounded-2xl">
                                            <h3 class="text-base font-semibold text-luxury-white">{{ __('Update DB') }}</h3>
                                            <p class="mt-1.5 text-sm font-semibold text-red-400">{{ __("This overwrites the current database with this backup's tables.") }}</p>
                                            <p class="mt-1 text-xs text-luxury-muted">{{ $backup['name'] }}</p>

                                            <form @submit.prevent="restore('{{ route('admin.system-tools.backups.restore', $backup['name']) }}')" class="mt-4">
                                                <label class="mb-1 block text-[11px] text-luxury-muted">{{ __('Type') }} <span class="font-mono text-luxury-white">RESTORE</span> {{ __('to confirm') }}</label>
                                                <input type="text" x-model="confirm" autocomplete="off" :disabled="restoring"
                                                    class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2.5 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold disabled:opacity-60">

                                                <p x-show="error" x-cloak x-text="error" class="mt-2 text-xs text-red-400"></p>

                                                <div class="mt-4 flex items-center gap-3">
                                                    <button type="submit" :disabled="confirm !== 'RESTORE' || restoring"
                                                        class="tap-scale flex flex-1 items-center justify-center gap-2 rounded-lg bg-red-500/90 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-500 disabled:cursor-not-allowed disabled:opacity-30">
                                                        <svg x-show="restoring" x-cloak class="h-3.5 w-3.5 animate-spin" viewBox="0 0 24 24" fill="none">
                                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                                        </svg>
                                                        <span x-text="restoring ? '{{ __('Restoring…') }}' : '{{ __('Update DB Now') }}'"></span>
                                                    </button>
                                                    <button type="button" @click="open = false; confirm = ''; error = null" :disabled="restoring"
                                                        class="tap-scale rounded-lg border border-luxury-border px-4 py-2.5 text-sm font-medium text-luxury-muted transition hover:text-luxury-white disabled:cursor-not-allowed disabled:opacity-50">
                                                        {{ __('Cancel') }}
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('admin.system-tools.backups.destroy', $backup['name']) }}" onsubmit="return confirm('{{ __('Delete this backup?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg border border-red-500/30 px-2.5 py-1.5 text-[11px] font-medium text-red-400 transition hover:bg-red-500/10">
                                        {{ __('Delete') }}
                                    </button>
                                </form>
                            @endpermission
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-luxury-muted">{{ __('No backups yet.') }}</p>
                @endforelse
            </div>

            @permission('system.delete')
                <div class="mt-5 rounded-xl border border-red-500/30 bg-red-500/5 p-4" x-data="{ open: false, confirm: '' }">
                    <p class="text-xs font-semibold text-red-400">{{ __('Danger Zone') }}</p>
                    <p class="mt-1 text-xs text-luxury-muted">
                        {{ __('You do not need this to restore a backup — "Update DB" above already replaces tables safely on its own. This button deletes every table, including sessions and admin accounts, which logs everyone out immediately and breaks every page on the site (login included) with no way to fix it from the browser — you\'d need server/terminal access to recover. Only use this for a full wipe-and-rebuild via CLI.') }}
                    </p>
                    <button type="button" @click="open = ! open" class="mt-3 rounded-lg border border-red-500/30 px-3 py-1.5 text-xs font-medium text-red-400 transition hover:bg-red-500/10">
                        {{ __('Delete All Tables') }}
                    </button>
                    <form method="POST" action="{{ route('admin.system-tools.database.drop-tables') }}" x-show="open" x-cloak class="mt-3">
                        @csrf
                        <label class="mb-1 block text-[11px] text-luxury-muted">{{ __('Type') }} <span class="font-mono text-luxury-white">DELETE ALL TABLES</span> {{ __('to confirm') }}</label>
                        <input type="text" name="confirm" x-model="confirm" autocomplete="off"
                            class="w-full max-w-xs rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-xs text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                        <button type="submit" :disabled="confirm !== 'DELETE ALL TABLES'"
                            class="mt-2 block rounded-lg bg-red-500/90 px-3 py-2 text-xs font-semibold text-white transition hover:bg-red-500 disabled:cursor-not-allowed disabled:opacity-30">
                            {{ __('Delete All Tables Now') }}
                        </button>
                    </form>
                </div>
            @endpermission
        </div>

        {{-- Deployment --}}
        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
            <h3 class="text-sm font-semibold text-luxury-white">{{ __('Deployment') }}</h3>
            <p class="mt-1 text-xs text-luxury-muted">{{ __('Run pending migrations or pull the latest Composer dependencies.') }}</p>

            @permission('system.edit')
                <form method="POST" action="{{ route('admin.system-tools.database.migrate') }}" class="mt-4">
                    @csrf
                    <x-admin.button type="submit" variant="secondary">{{ __('Run Migrations') }}</x-admin.button>
                </form>
            @endpermission

            @permission('system.delete')
                <form method="POST" action="{{ route('admin.system-tools.composer.update') }}" class="mt-3"
                    onsubmit="return confirm('{{ __('This can take several minutes and may break the site if a dependency update is incompatible. Continue?') }}');">
                    @csrf
                    <x-admin.button type="submit" variant="danger">{{ __('Run Composer Update') }}</x-admin.button>
                </form>
                <p class="mt-2 text-[11px] text-luxury-muted">{{ __('Runs') }} <code class="rounded bg-luxury-charcoal px-1 py-0.5">composer update</code> {{ __("on the server. This can take a few minutes — don't close this tab.") }}</p>
            @endpermission
        </div>
    </div>

    @push('scripts')
        <script>
            function backupUploader(url) {
                return {
                    filename: '',
                    uploading: false,
                    progress: 0,
                    error: null,

                    upload(file) {
                        if (! file) return;

                        this.filename = file.name;
                        this.uploading = true;
                        this.progress = 0;
                        this.error = null;

                        const formData = new FormData();
                        formData.append('file', file);

                        const xhr = new XMLHttpRequest();
                        xhr.open('POST', url);
                        xhr.setRequestHeader('Accept', 'application/json');
                        xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);

                        xhr.upload.addEventListener('progress', (event) => {
                            if (event.lengthComputable) {
                                this.progress = Math.round((event.loaded / event.total) * 100);
                            }
                        });

                        xhr.addEventListener('load', () => {
                            this.uploading = false;

                            if (xhr.status >= 200 && xhr.status < 300) {
                                window.location.reload();
                                return;
                            }

                            this.error = this.parseError(xhr.responseText, @js(__('Upload failed.')));
                        });

                        xhr.addEventListener('error', () => {
                            this.uploading = false;
                            this.error = @js(__('Upload failed. Please check your connection and try again.'));
                        });

                        xhr.send(formData);
                    },

                    parseError(responseText, fallback) {
                        try {
                            const data = JSON.parse(responseText);
                            if (data.message) return data.message;
                            if (data.errors) return Object.values(data.errors).flat().join(' ');
                        } catch (e) {
                            //
                        }

                        return fallback;
                    },
                };
            }

            function backupRestorer() {
                return {
                    open: false,
                    confirm: '',
                    restoring: false,
                    error: null,

                    async restore(url) {
                        this.restoring = true;
                        this.error = null;

                        try {
                            const response = await fetch(url, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                },
                                body: JSON.stringify({ confirm: this.confirm }),
                            });

                            const data = await response.json().catch(() => ({}));

                            if (response.ok) {
                                window.location.reload();
                                return;
                            }

                            this.error = data.message
                                || (data.errors ? Object.values(data.errors).flat().join(' ') : @js(__('Restore failed.')));
                        } catch (e) {
                            this.error = @js(__('Restore failed. Please check your connection and try again.'));
                        } finally {
                            this.restoring = false;
                        }
                    },
                };
            }
        </script>
    @endpush
</x-admin.layouts.app>
