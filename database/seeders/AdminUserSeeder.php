<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan role admin ada
        $adminRole = Role::firstOrCreate(
            ['slug' => 'admin'],
            ['name' => 'Administrator']
        );

        // Buat user admin baru jika belum ada
        $adminUser = User::firstOrCreate(
            ['email' => 'admin2@gmail.com'],
            [
                'name' => 'Admin Dua',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        // Hubungkan user ke role admin
        $adminUser->roles()->syncWithoutDetaching([$adminRole->id]);
    }
}