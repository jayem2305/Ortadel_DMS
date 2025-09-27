<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'user_id' => 'ADMIN00001',
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@gmail.com',
            'email_verified_at' => now(),
            'assigned_color' => '#000FFF0',
            'role_id' => 1,
            'groups' => ['Developers'],
            'created_by' => null,   // <-- set to null
            'last_updated_by' => null,
            'password' => 'admin123', // auto-hashed via cast
            'created_at' => now(),
        ]);

    }
}

