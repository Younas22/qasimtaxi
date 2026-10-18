@php
    $moduleLabels = config('permissions.modules');
    $actionLabels = config('permissions.actions');
    $selectedIds = $selectedIds ?? [];
@endphp

<div class="overflow-x-auto rounded-xl border border-luxury-border" x-data>
    <table class="w-full min-w-[640px] text-start text-sm">
        <thead class="bg-luxury-graphite">
            <tr>
                <th class="px-4 py-3 text-xs font-medium uppercase tracking-wider text-luxury-muted">
                    <label class="flex items-center gap-2">
                        <input type="checkbox"
                            class="rounded border-luxury-border bg-luxury-charcoal text-luxury-gold focus:ring-luxury-gold"
                            @change="$el.closest('table').querySelectorAll('input[type=checkbox][name]').forEach(cb => cb.checked = $el.checked)"
                            {{ $disabled ?? false ? 'disabled' : '' }}>
                        {{ __('Module') }}
                    </label>
                </th>
                @foreach ($actionLabels as $actionSlug => $actionLabel)
                    <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-luxury-muted">
                        <label class="flex flex-col items-center gap-1">
                            <input type="checkbox"
                                class="rounded border-luxury-border bg-luxury-charcoal text-luxury-gold focus:ring-luxury-gold"
                                @change="$el.closest('table').querySelectorAll('input[data-action={{ $actionSlug }}]').forEach(cb => cb.checked = $el.checked)"
                                {{ $disabled ?? false ? 'disabled' : '' }}>
                            {{ $actionLabel }}
                        </label>
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y divide-luxury-border/60">
            @foreach ($permissionGroups as $module => $permissions)
                <tr class="hover:bg-luxury-graphite/60">
                    <td class="px-4 py-3">
                        <label class="flex items-center gap-2 font-medium text-luxury-white">
                            <input type="checkbox"
                                class="rounded border-luxury-border bg-luxury-charcoal text-luxury-gold focus:ring-luxury-gold"
                                @change="$el.closest('tr').querySelectorAll('input[type=checkbox][name]').forEach(cb => cb.checked = $el.checked)"
                                {{ $disabled ?? false ? 'disabled' : '' }}>
                            {{ $moduleLabels[$module] ?? ucfirst($module) }}
                        </label>
                    </td>
                    @foreach ($actionLabels as $actionSlug => $actionLabel)
                        @php $permission = $permissions->firstWhere('action', $actionSlug); @endphp
                        <td class="px-4 py-3 text-center">
                            @if ($permission)
                                <input type="checkbox"
                                    name="permissions[]"
                                    value="{{ $permission->id }}"
                                    data-action="{{ $actionSlug }}"
                                    class="rounded border-luxury-border bg-luxury-charcoal text-luxury-gold focus:ring-luxury-gold"
                                    @checked(in_array($permission->id, $selectedIds))
                                    {{ $disabled ?? false ? 'disabled' : '' }}>
                            @else
                                <span class="text-luxury-muted">—</span>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@if ($disabled ?? false)
    <p class="mt-3 text-xs text-luxury-muted">{{ __('This is a system role and always has full access. Permissions cannot be modified.') }}</p>
@endif
