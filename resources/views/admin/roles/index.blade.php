<x-admin.layouts.app :title="__('Roles & Permissions')">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Roles & Permissions') }}</h2>
            <p class="mt-1 text-sm text-luxury-muted">{{ __('Manage enterprise roles and their module-level permissions.') }}</p>
        </div>

        @permission('roles.create')
            <a href="{{ route('admin.roles.create') }}">
                <x-admin.button type="button" variant="primary">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    {{ __('New Role') }}
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
                        <th class="px-6 py-3 font-medium">{{ __('Role') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Description') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Permissions') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Admins') }}</th>
                        <th class="px-6 py-3 text-end font-medium">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-luxury-border/60">
                    @forelse ($roles as $role)
                        <tr class="hover:bg-luxury-graphite">
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-luxury-white">{{ $role->name }}</span>
                                    @if ($role->is_system)
                                        <span class="rounded-full bg-luxury-secondary/10 px-2 py-0.5 text-[10px] uppercase tracking-wide text-luxury-secondary">{{ __('System') }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $role->description ?? '—' }}</td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $role->permissions_count }}</td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $role->admins_count }}</td>
                            <td class="px-6 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    @permission('roles.edit')
                                        <a href="{{ route('admin.roles.edit', $role) }}" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                            {{ __('Edit') }}
                                        </a>
                                    @endpermission

                                    @permission('roles.delete')
                                        @unless ($role->is_system)
                                            <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" onsubmit="return confirm('{{ __('Delete this role? Admins assigned to it will lose these permissions.') }}');">
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
                            <td colspan="5" class="px-6 py-10 text-center text-luxury-muted">{{ __('No roles created yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Mobile: cards --}}
    <div class="space-y-3 sm:hidden">
        @forelse ($roles as $role)
            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-4">
                <div class="flex items-center gap-2">
                    <p class="truncate text-sm font-medium text-luxury-white">{{ $role->name }}</p>
                    @if ($role->is_system)
                        <span class="shrink-0 rounded-full bg-luxury-secondary/10 px-2 py-0.5 text-[10px] uppercase tracking-wide text-luxury-secondary">{{ __('System') }}</span>
                    @endif
                </div>
                @if ($role->description)
                    <p class="mt-1 text-xs text-luxury-muted">{{ $role->description }}</p>
                @endif

                <div class="mt-3 grid grid-cols-2 gap-3 border-t border-luxury-border pt-3 text-xs">
                    <div>
                        <p class="text-luxury-muted">{{ __('Permissions') }}</p>
                        <p class="mt-0.5 font-medium text-luxury-white">{{ $role->permissions_count }}</p>
                    </div>
                    <div>
                        <p class="text-luxury-muted">{{ __('Admins') }}</p>
                        <p class="mt-0.5 font-medium text-luxury-white">{{ $role->admins_count }}</p>
                    </div>
                </div>

                <div class="mt-3 flex flex-wrap items-center gap-2 border-t border-luxury-border pt-3">
                    @permission('roles.edit')
                        <a href="{{ route('admin.roles.edit', $role) }}" class="tap-scale rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                            {{ __('Edit') }}
                        </a>
                    @endpermission
                    @permission('roles.delete')
                        @unless ($role->is_system)
                            <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" onsubmit="return confirm('{{ __('Delete this role? Admins assigned to it will lose these permissions.') }}');" class="ms-auto">
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
                {{ __('No roles created yet.') }}
            </div>
        @endforelse
    </div>
</x-admin.layouts.app>
