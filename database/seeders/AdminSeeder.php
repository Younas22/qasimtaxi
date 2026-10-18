<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Role;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = Admin::firstOrCreate(
            ['email' => 'admin@limoschedule.test'],
            [
                'name' => 'Super Admin',
                'phone' => '+1 555-0100',
                'status' => true,
                'password' => 'password',
            ]
        );

        $superAdminRole = Role::where('slug', 'super-admin')->first();

        if ($superAdminRole) {
            $admin->roles()->syncWithoutDetaching($superAdminRole->id);
        }
    }
}
