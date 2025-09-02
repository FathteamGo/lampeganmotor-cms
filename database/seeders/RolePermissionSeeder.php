<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cache permission dan role biar fresh
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        /*
        |--------------------------------------------------------------------------
        | Definisikan Permissions
        |--------------------------------------------------------------------------
        */
        $permissions = [
            // Purchase
            'view_purchase',
            'create_purchase',
            'edit_purchase',
            'delete_purchase',

            // Sales
            'view_sale',
            'create_sale',
            'edit_sale',
            'delete_sale',

            // User Management
            'view_user',
            'create_user',
            'edit_user',
            'delete_user',

            // Dashboard
            'view_dashboard',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        /*
        |--------------------------------------------------------------------------
        | Definisikan Roles
        |--------------------------------------------------------------------------
        */
        $roles = [
            'owner' => [
                'view_dashboard',
                'view_purchase', 'create_purchase', 'edit_purchase', 'delete_purchase',
                'view_sale', 'create_sale', 'edit_sale', 'delete_sale',
                'view_user', 'create_user', 'edit_user', 'delete_user',
            ],
            'manager' => [
                'view_dashboard',
                'view_purchase', 'create_purchase', 'edit_purchase',
                'view_sale', 'create_sale', 'edit_sale',
                'view_user',
            ],
            'staff' => [
                'view_dashboard',
                'view_purchase', 'create_purchase',
                'view_sale',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($rolePermissions);
        }

        /*
        |--------------------------------------------------------------------------
        | Assign role ke user default (optional)
        |--------------------------------------------------------------------------
        */
        if ($user = \App\Models\User::first()) {
            $user->assignRole('owner'); // user pertama jadi owner
        }
    }
}
