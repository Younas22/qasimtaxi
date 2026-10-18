<x-admin.layouts.app :title="__('Edit Role')">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Edit Role') }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ __('Update the role details and its module permissions.') }}</p>
    </div>

    <form method="POST" action="{{ route('admin.roles.update', $role) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <x-admin.input-label for="name" value="{{ __('Role Name') }}" />
                    <x-admin.text-input id="name" name="name" type="text" value="{{ old('name', $role->name) }}" required autofocus @if ($role->is_system) readonly @endif />
                    <x-admin.input-error :messages="$errors->get('name')" />
                </div>

                <div>
                    <x-admin.input-label for="description" value="{{ __('Description') }}" />
                    <x-admin.text-input id="description" name="description" type="text" value="{{ old('description', $role->description) }}" />
                    <x-admin.input-error :messages="$errors->get('description')" />
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
            <h3 class="mb-4 text-sm font-semibold text-luxury-white">{{ __('Permissions') }}</h3>
            @include('admin.roles._permission-matrix', ['permissionGroups' => $permissionGroups, 'selectedIds' => old('permissions', $selectedIds), 'disabled' => $role->is_system])
            <x-admin.input-error :messages="$errors->get('permissions')" class="mt-3" />
        </div>

        <div class="flex items-center gap-3">
            <x-admin.button type="submit" variant="primary">{{ __('Save Changes') }}</x-admin.button>
            <a href="{{ route('admin.roles.index') }}" class="text-sm text-luxury-muted hover:text-luxury-white">{{ __('Cancel') }}</a>
        </div>
    </form>
</x-admin.layouts.app>
