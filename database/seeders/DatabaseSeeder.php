<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Seed categories first
        $this->call(CategorySeeder::class);

        // Create one user per role for development/testing
        $users = [
            [
                'name'         => 'Admin User',
                'email'        => 'admin@roadwatch.test',
                'phone'        => '9000000001',
                'password'     => Hash::make('password'),
                'role'         => 'admin',
                'is_active'    => true,
                'is_verified'  => true,
                'password_set' => true,
                'email_verified_at' => now(),
            ],
            [
                'name'         => 'Engineer User',
                'email'        => 'engineer@roadwatch.test',
                'phone'        => '9000000002',
                'password'     => Hash::make('password'),
                'role'         => 'engineer',
                'is_active'    => true,
                'is_verified'  => true,
                'password_set' => true,
                'email_verified_at' => now(),
            ],
            [
                'name'         => 'Citizen User',
                'email'        => 'citizen@roadwatch.test',
                'phone'        => '9000000003',
                'password'     => Hash::make('password'),
                'role'         => 'citizen',
                'is_active'    => true,
                'is_verified'  => false,
                'password_set' => true,
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $userData) {
            User::firstOrCreate(['email' => $userData['email']], $userData);
        }

        $this->command->info('✅ Default users created (password: "password")');
    }
}
