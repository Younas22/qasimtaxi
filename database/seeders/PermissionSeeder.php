<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (config('permissions.modules') as $moduleSlug => $moduleLabel) {
            foreach (config('permissions.actions') as $actionSlug => $actionLabel) {
                Permission::firstOrCreate([
                    'slug' => "{$moduleSlug}.{$actionSlug}",
                ], [
                    'module' => $moduleSlug,
                    'action' => $actionSlug,
                ]);
            }
        }
    }
}
