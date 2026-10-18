<x-admin.layouts.app :title="__('Languages')">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Languages') }}</h2>
            <p class="mt-1 text-sm text-luxury-muted">{{ __('Manage the languages available across the admin panel.') }}</p>
        </div>

        @permission('languages.create')
            <a href="{{ route('admin.languages.create') }}">
                <x-admin.button type="button" variant="primary">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    {{ __('Add Language') }}
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
                        <th class="px-6 py-3 font-medium">{{ __('Language') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Code') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Direction') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Status') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Strings') }}</th>
                        <th class="px-6 py-3 text-end font-medium">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-luxury-border/60">
                    @forelse ($languages as $language)
                        <tr class="hover:bg-luxury-graphite">
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-luxury-border bg-luxury-graphite text-xs font-semibold uppercase text-luxury-muted">
                                        @if ($language->flag_url)
                                            <img src="{{ $language->flag_url }}" alt="{{ $language->name }}" class="h-full w-full object-cover">
                                        @else
                                            {{ $language->code }}
                                        @endif
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium text-luxury-white">{{ $language->name }}</span>
                                            @if ($language->is_default)
                                                <span class="rounded-full bg-luxury-gold/10 px-2 py-0.5 text-[10px] uppercase tracking-wide text-luxury-gold">{{ __('Default') }}</span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-luxury-muted">{{ $language->native_name }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-3 text-luxury-muted">{{ strtoupper($language->code) }}</td>
                            <td class="px-6 py-3 text-luxury-muted uppercase">{{ $language->direction }}</td>
                            <td class="px-6 py-3">
                                <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $language->is_active ? 'bg-emerald-500/10 text-emerald-400' : 'bg-luxury-slate text-luxury-muted' }}">
                                    {{ $language->is_active ? __('Active') : __('Disabled') }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $language->translations_count }}</td>
                            <td class="px-6 py-3">
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    @permission('languages.edit')
                                        <a href="{{ route('admin.languages.translations.edit', $language) }}" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                            {{ __('Translate') }}
                                        </a>

                                        <a href="{{ route('admin.languages.edit', $language) }}" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                            {{ __('Edit') }}
                                        </a>

                                        @unless ($language->is_default)
                                            <form method="POST" action="{{ route('admin.languages.default', $language) }}">
                                                @csrf
                                                <button type="submit" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-secondary hover:text-luxury-secondary">
                                                    {{ __('Set Default') }}
                                                </button>
                                            </form>
                                        @endunless

                                        @unless ($language->is_default)
                                            <form method="POST" action="{{ route('admin.languages.toggle', $language) }}">
                                                @csrf
                                                <button type="submit" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                                    {{ $language->is_active ? __('Disable') : __('Enable') }}
                                                </button>
                                            </form>
                                        @endunless
                                    @endpermission

                                    @permission('languages.delete')
                                        @unless ($language->is_default)
                                            <form method="POST" action="{{ route('admin.languages.destroy', $language) }}" onsubmit="return confirm('{{ __('Delete this language and all of its translations?') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-lg border border-red-500/30 px-3 py-1.5 text-xs font-medium text-red-400 transition hover:bg-red-500/10">
                                                    {{ __('Delete') }}
                                                </button>
                                            </form>
                                        @endunless
                                    @endpermission
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-luxury-muted">{{ __('No languages configured yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Mobile: cards --}}
    <div class="space-y-3 sm:hidden">
        @forelse ($languages as $language)
            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-4">
                <div class="flex items-start gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-luxury-border bg-luxury-graphite text-xs font-semibold uppercase text-luxury-muted">
                        @if ($language->flag_url)
                            <img src="{{ $language->flag_url }}" alt="{{ $language->name }}" class="h-full w-full object-cover">
                        @else
                            {{ $language->code }}
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <span class="truncate text-sm font-medium text-luxury-white">{{ $language->name }}</span>
                            @if ($language->is_default)
                                <span class="shrink-0 rounded-full bg-luxury-gold/10 px-2 py-0.5 text-[10px] uppercase tracking-wide text-luxury-gold">{{ __('Default') }}</span>
                            @endif
                        </div>
                        <p class="truncate text-xs text-luxury-muted">{{ $language->native_name }} &middot; {{ strtoupper($language->code) }} &middot; {{ strtoupper($language->direction) }}</p>
                    </div>
                    <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-medium {{ $language->is_active ? 'bg-emerald-500/10 text-emerald-400' : 'bg-luxury-slate text-luxury-muted' }}">
                        {{ $language->is_active ? __('Active') : __('Disabled') }}
                    </span>
                </div>

                <p class="mt-3 border-t border-luxury-border pt-3 text-xs text-luxury-muted">{{ __(':count translated strings', ['count' => $language->translations_count]) }}</p>

                <div class="mt-3 flex flex-wrap items-center gap-2 border-t border-luxury-border pt-3">
                    @permission('languages.edit')
                        <a href="{{ route('admin.languages.translations.edit', $language) }}" class="tap-scale rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                            {{ __('Translate') }}
                        </a>
                        <a href="{{ route('admin.languages.edit', $language) }}" class="tap-scale rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                            {{ __('Edit') }}
                        </a>
                        @unless ($language->is_default)
                            <form method="POST" action="{{ route('admin.languages.default', $language) }}">
                                @csrf
                                <button type="submit" class="tap-scale rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-secondary hover:text-luxury-secondary">
                                    {{ __('Set Default') }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.languages.toggle', $language) }}">
                                @csrf
                                <button type="submit" class="tap-scale rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                    {{ $language->is_active ? __('Disable') : __('Enable') }}
                                </button>
                            </form>
                        @endunless
                    @endpermission
                    @permission('languages.delete')
                        @unless ($language->is_default)
                            <form method="POST" action="{{ route('admin.languages.destroy', $language) }}" onsubmit="return confirm('{{ __('Delete this language and all of its translations?') }}');" class="ms-auto">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="tap-scale rounded-lg border border-red-500/30 px-3 py-1.5 text-xs font-medium text-red-400 transition hover:bg-red-500/10">
                                    {{ __('Delete') }}
                                </button>
                            </form>
                        @endunless
                    @endpermission
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-10 text-center text-sm text-luxury-muted">
                {{ __('No languages configured yet.') }}
            </div>
        @endforelse
    </div>
</x-admin.layouts.app>
