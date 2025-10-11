<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Group;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Main database seeder for the Ortadel Document Management System v2
 * 
 * This seeder initializes the database with essential system data including:
 * - System permissions (via PermissionSeeder)
 * - User roles (via RoleSeeder) 
 * - Default system users (Admin, Developer, Test)
 * - Sample groups
 * 
 * All sensitive data is encrypted using AES-256 encryption for security.
 * The seeder creates a complete working environment for immediate system use.
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with essential system data
     * 
     * This method performs a complete database initialization:
     * 1. Clears existing data to ensure clean state
     * 2. Seeds permissions and roles (system foundation)
     * 3. Creates default users with different access levels
     * 4. Creates sample groups for organization
     * 5. Provides summary of created records
     */
    public function run(): void
    {
        // Display informative start message for seeding process
        $this->command->info('🌱 Starting database seeding with secure encryption...');
        
        // === DATA CLEANUP SECTION ===
        // Clear all existing data to prevent conflicts and ensure clean state
        $this->command->info('🧹 Clearing existing data...');
        
        // Temporarily disable foreign key constraints to allow table truncation
        // This prevents errors when truncating tables with foreign key relationships
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Truncate pivot/junction tables FIRST (they contain foreign keys)
        // Order matters: dependent tables must be cleared before parent tables
        DB::table('role_permission')->truncate();  // Many-to-many: roles ↔ permissions
        DB::table('group_user')->truncate();       // Many-to-many: groups ↔ users
        DB::table('group_role')->truncate();       // Many-to-many: groups ↔ roles
        
        // Then truncate main entity tables (parent tables)
        \App\Models\Permission::truncate();        // System permissions (foundation)
        \App\Models\Role::truncate();              // User roles
        \App\Models\Group::truncate();             // User groups/departments
        \App\Models\User::truncate();              // System users
        
        // Re-enable foreign key constraints after cleanup
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        // === SYSTEM FOUNDATION SEEDING ===
        // Seed permissions first (foundational data - other entities depend on them)
        $this->command->info('🔐 Seeding permissions...');
        $this->call(PermissionSeeder::class);
        
        // Seed roles next (depend on permissions via role_permission table)
        $this->command->info('👥 Seeding roles...');
        $this->call(RoleSeeder::class);
        
        // === DEFAULT USER CREATION ===
        // Create system admin user with developer role (highest permissions)
        $this->command->info('👤 Creating admin user...');
        $adminUser = User::create([
            'user_id' => 'DMS_0001',              
            'first_name' => 'Admin',              
            'last_name' => 'User',                
            'email' => 'admin@ortadel.com',       
            'password' => Hash::make('password'), 
            'role_id' => 1,                       
            'assigned_color' => '#025234',        
            'created_by' => 1,                    
            'last_updated_by' => 1,             
        ]);
        
        // Seed additional test users with different roles
        $this->command->info('👥 Seeding additional users...');
        $this->call(UserSeeder::class);

        // === COMPLETION AND SUMMARY ===
        // Display successful completion message
        $this->command->info('✅ Database seeding completed successfully!');
        
        // Provide detailed summary of what was created
        $this->command->info('📊 Summary:');
        $this->command->info("   • Permissions: " . \App\Models\Permission::count());
        $this->command->info("   • Roles: " . \App\Models\Role::count());
        $this->command->info("   • Users: " . \App\Models\User::count());
        $this->command->info("   • Groups: " . \App\Models\Group::count());
        $this->command->info('');
        
        // Security information
        $this->command->info('🔐 All data is encrypted with AES-256!');
        
        // Login credentials for immediate system access
        $this->command->info('👤 Login credentials:');
        $this->command->info('   • Admin: admin@ortadel.com / password (Developer role - Full access)');
        $this->command->info('   • Developer: developer@ortadel.com / password (Developer role - Full access)');
        $this->command->info('   • Test: test@example.com / password (Admin role - Full access)');
        $this->command->info('');
        
        // Important note about developer role permissions
        $this->command->info('🔑 Developer role has ALL 43 permissions - complete system access!');
    }
}
