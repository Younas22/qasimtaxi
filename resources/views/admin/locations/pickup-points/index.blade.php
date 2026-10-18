<x-admin.layouts.app :title="__('Pickup Points')">
    @include('admin.locations._tabs')

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Pickup Points') }}</h2>
            <p class="mt-1 text-sm text-luxury-muted">{{ __('Manage hotels, landmarks, and other named pickup points.') }}</p>
        </div>

        @permission('locations.create')
            <a href="{{ route('admin.locations.pickup-points.create') }}">
                <x-admin.button type="button" variant="primary">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    {{ __('Add Pickup Point') }}
                </x-admin.button>
            </a>
        @endpermission
    </div>

    <div class="overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal">
        <div class="overflow-x-auto">
            <table class="w-full text-start text-sm">
                <thead>
                    <tr class="border-b border-luxury-border text-xs uppercase tracking-wider text-luxury-muted">
                        <th class="px-6 py-3 font-medium">{{ __('Pickup Point') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Type') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('City') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Status') }}</th>
                        <th class="px-6 py-3 text-end font-medium">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-luxury-border/60">
                    @forelse ($items as $point)
                        <tr class="hover:bg-luxury-graphite">
                            <td class="px-6 py-3">
                                <p class="font-medium text-luxury-white">{{ $point->name }}</p>
                                @if ($point->address)
                                    <p class="text-xs text-luxury-muted">{{ $point->address }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-luxury-muted capitalize">{{ $point->type }}</td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $point->city->name }}</td>
                            <td class="px-6 py-3"><x-admin.status-badge :active="$point->is_active" /></td>
                            <td class="px-6 py-3">
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    @permission('locations.edit')
                                        <a href="{{ route('admin.locations.pickup-points.edit', $point) }}" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                            {{ __('Edit') }}
                                        </a>
                                        <form method="POST" action="{{ route('admin.locations.pickup-points.toggle', $point) }}">
                                            @csrf
                                            <button type="submit" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                                {{ $point->is_active ? __('Disable') : __('Enable') }}
                                            </button>
                                        </form>
                                    @endpermission

                                    @permission('locations.delete')
                                        <form method="POST" action="{{ route('admin.locations.pickup-points.destroy', $point) }}" onsubmit="return confirm('{{ __('Delete this pickup point?') }}');">
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
                            <td colspan="5" class="px-6 py-10 text-center text-luxury-muted">{{ __('No pickup points added yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin.layouts.app>
