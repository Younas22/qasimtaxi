<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $allSlugs = Permission::pluck('slug')->all();
        $modules = array_keys(config('permissions.modules'));

        $roles = [
            [
                'name' => 'Super Admin',
                'slug' => 'super-admin',
                'description' => 'Unrestricted access to every module and setting.',
                'is_system' => true,
                'permissions' => $allSlugs,
            ],
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Full operational access, excluding role deletion.',
                'is_system' => false,
                'permissions' => array_values(array_filter($allSlugs, fn ($slug) => $slug !== 'roles.delete')),
            ],
            [
                'name' => 'Manager',
                'slug' => 'manager',
                'description' => 'Oversees daily operations with view, create, edit and export access.',
                'is_system' => false,
                'permissions' => $this->slugsFor($modules, ['view', 'create', 'edit', 'export']),
            ],
            [
                'name' => 'Booking Agent',
                'slug' => 'booking-agent',
                'description' => 'Manages bookings and views customer information.',
                'is_system' => false,
                'permissions' => [
                    'dashboard.view',
                    'bookings.view', 'bookings.create', 'bookings.edit', 'bookings.export',
                    'customers.view',
                    'messages.view', 'messages.delete',
                ],
            ],
            [
                'name' => 'Driver Manager',
                'slug' => 'driver-manager',
                'description' => 'Manages the driver roster and vehicle fleet.',
                'is_system' => false,
                'permissions' => [
                    'dashboard.view',
                    'drivers.view', 'drivers.create', 'drivers.edit', 'drivers.delete', 'drivers.export',
                    'vehicles.view', 'vehicles.edit',
                ],
            ],
            [
                'name' => 'Content Manager',
                'slug' => 'content-manager',
                'description' => 'Manages website content such as blogs and pages.',
                'is_system' => false,
                'permissions' => [
                    'dashboard.view',
                    'content.view', 'content.create', 'content.edit', 'content.delete', 'content.export',
                    'routes.view', 'routes.create', 'routes.edit', 'routes.delete', 'routes.export',
                ],
            ],
        ];

        foreach ($roles as $definition) {
            $permissionSlugs = $definition['permissions'];
            unset($definition['permissions']);

            $role = Role::firstOrCreate(['slug' => $definition['slug']], $definition);
            $role->syncPermissionsBySlug($permissionSlugs);
        }
    }

    /**
     * Build permission slugs for the given modules limited to the given actions.
     *
     * @param  array<int, string>  $modules
     * @param  array<int, string>  $actions
     * @return array<int, string>
     */
    private function slugsFor(array $modules, array $actions): array
    {
        $slugs = [];

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                $slugs[] = "{$module}.{$action}";
            }
        }

        return $slugs;
    }
}
