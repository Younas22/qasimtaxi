<x-admin.layouts.app :title="__('Countries')">
    @include('admin.locations._tabs')

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Countries') }}</h2>
            <p class="mt-1 text-sm text-luxury-muted">{{ __('Manage the countries your fleet operates in.') }}</p>
        </div>

        @permission('locations.create')
            <a href="{{ route('admin.locations.countries.create') }}">
                <x-admin.button type="button" variant="primary">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    {{ __('Add Country') }}
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
                        <th class="px-6 py-3 font-medium">{{ __('Country') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Code') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('States') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Status') }}</th>
                        <th class="px-6 py-3 text-end font-medium">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-luxury-border/60">
                    @forelse ($items as $country)
                        <tr class="hover:bg-luxury-graphite">
                            <td class="px-6 py-3 font-medium text-luxury-white">{{ $country->name }}</td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $country->code }}</td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $country->states()->count() }}</td>
                            <td class="px-6 py-3"><x-admin.status-badge :active="$country->is_active" /></td>
                            <td class="px-6 py-3">
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    @permission('locations.edit')
                                        <a href="{{ route('admin.locations.countries.edit', $country) }}" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                            {{ __('Edit') }}
                                        </a>
                                        <form method="POST" action="{{ route('admin.locations.countries.toggle', $country) }}">
                                            @csrf
                                            <button type="submit" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                                {{ $country->is_active ? __('Disable') : __('Enable') }}
                                            </button>
                                        </form>
                                    @endpermission

                                    @permission('locations.delete')
                                        <form method="POST" action="{{ route('admin.locations.countries.destroy', $country) }}" onsubmit="return confirm('{{ __('Delete this country?') }}');">
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
                            <td colspan="5" class="px-6 py-10 text-center text-luxury-muted">{{ __('No countries added yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Mobile: cards --}}
    <div class="space-y-3 sm:hidden">
        @forelse ($items as $country)
            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium text-luxury-white">{{ $country->name }}</p>
                        <p class="text-xs text-luxury-muted">{{ $country->code }} &middot; {{ __(':count states', ['count' => $country->states()->count()]) }}</p>
                    </div>
                    <x-admin.status-badge :active="$country->is_active" />
                </div>

                <div class="mt-3 flex flex-wrap items-center gap-2 border-t border-luxury-border pt-3">
                    @permission('locations.edit')
                        <a href="{{ route('admin.locations.countries.edit', $country) }}" class="tap-scale rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                            {{ __('Edit') }}
                        </a>
                        <form method="POST" action="{{ route('admin.locations.countries.toggle', $country) }}">
                            @csrf
                            <button type="submit" class="tap-scale rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                {{ $country->is_active ? __('Disable') : __('Enable') }}
                            </button>
                        </form>
                    @endpermission
                    @permission('locations.delete')
                        <form method="POST" action="{{ route('admin.locations.countries.destroy', $country) }}" onsubmit="return confirm('{{ __('Delete this country?') }}');" class="ms-auto">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="tap-scale rounded-lg border border-red-500/30 px-3 py-1.5 text-xs font-medium text-red-400 transition hover:bg-red-500/10">
                                {{ __('Delete') }}
                            </button>
                        </form>
                    @endpermission
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-10 text-center text-sm text-luxury-muted">
                {{ __('No countries added yet.') }}
            </div>
        @endforelse
    </div>
</x-admin.layouts.app>
