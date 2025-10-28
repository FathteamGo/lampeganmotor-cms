<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan package spatie/laravel-permission terpasang dan di-publish
        $roles = ['superadmin', 'owner'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        $superadmin = User::firstOrCreate(
            ['email' => 'superadmin@lampeganmotor.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('12345678'),
                'email_verified_at' => now(),
            ]
        );
        $superadmin->assignRole('superadmin');

        $owner = User::firstOrCreate(
            ['email' => 'owner@lampeganmotor.com'],
            [
                'name' => 'Owner',
                'password' => Hash::make('12345678'),
                'email_verified_at' => now(),
            ]
        );
        $owner->assignRole('owner');
    }
}
