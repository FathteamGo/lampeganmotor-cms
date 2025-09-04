<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat role
        $owner = Role::firstOrCreate(['name' => 'owner']);
        $admin = Role::firstOrCreate(['name' => 'admin']);

        // 2. Buat permission (bisa ditambah sesuai kebutuhan)
        $permissions = [
            'view reports',
            'manage purchases',
            'manage sales',
            'manage vehicles',
            'manage users',
            'delete records',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // 3. Assign permission ke role
        $owner->syncPermissions($permissions); // Owner full access

        $admin->syncPermissions([
            'manage purchases',
            'manage sales',
            'manage vehicles',
            'manage users',
            // admin TIDAK dapat delete dan view reports
        ]);
    }
}
