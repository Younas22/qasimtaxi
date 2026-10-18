<x-admin.layouts.app :title="__('Service Areas')">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Service Areas') }}</h2>
            <p class="mt-1 text-sm text-luxury-muted">{{ __('The towns and cities you serve, shown on the website.') }}</p>
        </div>

        @permission('areas.create')
            <a href="{{ route('admin.areas.create') }}">
                <x-admin.button type="button" variant="primary">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    {{ __('Add Area') }}
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
                        <th class="px-6 py-3 font-medium">{{ __('Area') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Status') }}</th>
                        <th class="px-6 py-3 text-end font-medium">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-luxury-border/60">
                    @forelse ($areas as $area)
                        <tr class="hover:bg-luxury-graphite">
                            <td class="px-6 py-3">
                                @permission('areas.edit')
                                    <div class="flex items-center gap-1">
                                        <form method="POST" action="{{ route('admin.areas.move-up', $area) }}">
                                            @csrf
                                            <button type="submit" @if ($loop->first) disabled @endif
                                                class="flex h-7 w-7 items-center justify-center rounded-md border border-luxury-border text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold disabled:cursor-not-allowed disabled:opacity-30">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" />
                                                </svg>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.areas.move-down', $area) }}">
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
                                <p class="font-medium text-luxury-white">{{ $area->name }}</p>
                                @if ($area->description)
                                    <p class="max-w-xs truncate text-xs text-luxury-muted">{{ $area->description }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-3"><x-admin.status-badge :active="$area->is_active" /></td>
                            <td class="px-6 py-3">
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    <a href="{{ route('areas.show', $area) }}" target="_blank" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                        {{ __('View Page') }}
                                    </a>
                                    @permission('areas.edit')
                                        <a href="{{ route('admin.areas.edit', $area) }}" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                            {{ __('Edit') }}
                                        </a>
                                        <form method="POST" action="{{ route('admin.areas.toggle', $area) }}">
                                            @csrf
                                            <button type="submit" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                                {{ $area->is_active ? __('Disable') : __('Enable') }}
                                            </button>
                                        </form>
                                    @endpermission

                                    @permission('areas.delete')
                                        <form method="POST" action="{{ route('admin.areas.destroy', $area) }}" onsubmit="return confirm('{{ __('Delete this area?') }}');">
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
                            <td colspan="4" class="px-6 py-10 text-center text-luxury-muted">{{ __('No areas added yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin.layouts.app>
