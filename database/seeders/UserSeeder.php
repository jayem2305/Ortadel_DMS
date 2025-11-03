<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // === DEVELOPER ROLE USERS (Role ID: 1) ===
        // These users have full system access - all 43 permissions
        
        $developer1 = User::create([
            'user_id' => 'DEV_0001',
            'first_name' => 'John',
            'last_name' => 'Developer',
            'email' => 'dev@ortadel.com',
            'email_verified_at' => now(),
            'assigned_color' => '#6366f1',
            'role_id' => 1, // Developer role
            'created_by' => 1,
            'last_updated_by' => 1,
            'password' => Hash::make('password'),
            'created_at' => now(),
        ]);

        // === ADMIN ROLE USERS (Role ID: 2) ===
        // These users have full access like developers
        
        $admin1 = User::create([
            'user_id' => 'ADM_0001',
            'first_name' => 'Michael',
            'last_name' => 'Admin',
            'email' => 'admin1@ortadel.com',
            'email_verified_at' => now(),
            'assigned_color' => '#2563eb',
            'role_id' => 2, // Admin role
            'created_by' => 1,
            'last_updated_by' => 1,
            'password' => Hash::make('password'),
            'created_at' => now(),
        ]);

        // === MANAGER ROLE USERS (Role ID: 3) ===
        // These users have restricted access - cannot manage users, roles, permissions
        
        $manager1 = User::create([
            'user_id' => 'MGR_0001',
            'first_name' => 'David',
            'last_name' => 'Manager',
            'email' => 'manager@ortadel.com',
            'email_verified_at' => now(),
            'assigned_color' => '#f59e0b',
            'role_id' => 3, // Manager role
            'created_by' => 1,
            'last_updated_by' => 1,
            'password' => Hash::make('password'),
            'created_at' => now(),
        ]);

        // === STAFF ROLE USERS (Role ID: 4) ===
        // These users have minimal access - cannot manage users, access controls, delete files, etc.
        
        $staff1 = User::create([
            'user_id' => 'STF_0001',
            'first_name' => 'Emily',
            'last_name' => 'Staff',
            'email' => 'staff@ortadel.com',
            'email_verified_at' => now(),
            'assigned_color' => '#10b981',
            'role_id' => 4, // Staff role
            'created_by' => 1,
            'last_updated_by' => 1,
            'password' => Hash::make('password'),
            'created_at' => now(),
        ]);

        // === REVIEWER ROLE USERS (Role ID: 5) ===
        // These users can review and comment on documents
        
        $reviewer1 = User::create([
            'user_id' => 'REV_0001',
            'first_name' => 'Sarah',
            'last_name' => 'Reviewer',
            'email' => 'reviewer@ortadel.com',
            'email_verified_at' => now(),
            'assigned_color' => '#8b5cf6',
            'role_id' => 5, // Reviewer role
            'created_by' => 1,
            'last_updated_by' => 1,
            'password' => Hash::make('password'),
            'created_at' => now(),
        ]);

        // === APPROVER ROLE USERS (Role ID: 6) ===
        // These users can approve and reject documents
        
        $approver1 = User::create([
            'user_id' => 'APV_0001',
            'first_name' => 'Robert',
            'last_name' => 'Approver',
            'email' => 'approver@ortadel.com',
            'email_verified_at' => now(),
            'assigned_color' => '#ec4899',
            'role_id' => 6, // Approver role
            'created_by' => 1,
            'last_updated_by' => 1,
            'password' => Hash::make('password'),
            'created_at' => now(),
        ]);

    }
}

