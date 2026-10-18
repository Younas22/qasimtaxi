<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        $roles = Role::withCount(['permissions', 'admins'])->orderBy('name')->get();

        return view('admin.roles.index', compact('roles'));
    }

    public function create(): View
    {
        $permissionGroups = $this->permissionGroups();

        return view('admin.roles.create', compact('permissionGroups'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateRole($request);

        $role = Role::create([
            'name' => $data['name'],
            'slug' => $this->uniqueSlug($data['name']),
            'description' => $data['description'] ?? null,
        ]);

        $role->permissions()->sync($data['permissions'] ?? []);

        return redirect()->route('admin.roles.index')->with('status', "Role \"{$role->name}\" created successfully.");
    }

    public function edit(Role $role): View
    {
        $role->load('permissions');

        $permissionGroups = $this->permissionGroups();
        $selectedIds = $role->permissions->pluck('id')->all();

        return view('admin.roles.edit', compact('role', 'permissionGroups', 'selectedIds'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $data = $this->validateRole($request, $role);

        $role->update([
            'name' => $role->is_system ? $role->name : $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        $role->permissions()->sync($role->is_system ? Permission::pluck('id') : ($data['permissions'] ?? []));

        return redirect()->route('admin.roles.index')->with('status', "Role \"{$role->name}\" updated successfully.");
    }

    public function destroy(Role $role): RedirectResponse
    {
        abort_if($role->is_system, 403, 'System roles cannot be deleted.');

        $role->delete();

        return back()->with('status', 'Role deleted successfully.');
    }

    private function validateRole(Request $request, ?Role $role = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;

        while (Role::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    private function permissionGroups()
    {
        return Permission::orderBy('module')->orderBy('action')->get()->groupBy('module');
    }
}
