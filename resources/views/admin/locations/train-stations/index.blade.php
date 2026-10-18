<x-admin.layouts.app :title="__('Train Stations')">
    @include('admin.locations._tabs')

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Train Stations') }}</h2>
            <p class="mt-1 text-sm text-luxury-muted">{{ __('Manage train station pickup/drop-off locations.') }}</p>
        </div>

        @permission('locations.create')
            <a href="{{ route('admin.locations.train-stations.create') }}">
                <x-admin.button type="button" variant="primary">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    {{ __('Add Train Station') }}
                </x-admin.button>
            </a>
        @endpermission
    </div>

    <div class="overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal">
        <div class="overflow-x-auto">
            <table class="w-full text-start text-sm">
                <thead>
                    <tr class="border-b border-luxury-border text-xs uppercase tracking-wider text-luxury-muted">
                        <th class="px-6 py-3 font-medium">{{ __('Station') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Code') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('City') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Country') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Status') }}</th>
                        <th class="px-6 py-3 text-end font-medium">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-luxury-border/60">
                    @forelse ($items as $station)
                        <tr class="hover:bg-luxury-graphite">
                            <td class="px-6 py-3 font-medium text-luxury-white">{{ $station->name }}</td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $station->code ?? '—' }}</td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $station->city->name }}</td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $station->city->country->name }}</td>
                            <td class="px-6 py-3"><x-admin.status-badge :active="$station->is_active" /></td>
                            <td class="px-6 py-3">
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    @permission('locations.edit')
                                        <a href="{{ route('admin.locations.train-stations.edit', $station) }}" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                            {{ __('Edit') }}
                                        </a>
                                        <form method="POST" action="{{ route('admin.locations.train-stations.toggle', $station) }}">
                                            @csrf
                                            <button type="submit" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                                {{ $station->is_active ? __('Disable') : __('Enable') }}
                                            </button>
                                        </form>
                                    @endpermission

                                    @permission('locations.delete')
                                        <form method="POST" action="{{ route('admin.locations.train-stations.destroy', $station) }}" onsubmit="return confirm('{{ __('Delete this train station?') }}');">
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
                            <td colspan="6" class="px-6 py-10 text-center text-luxury-muted">{{ __('No train stations added yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin.layouts.app>
