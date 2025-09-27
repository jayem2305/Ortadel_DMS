<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Fetch all permissions grouped by module
        $permissions = Permission::all()->groupBy('module');

        // Helper to assign all permissions
        $fullAccess = fn() => Permission::pluck('id')->toArray();

        // Developer (full access)
        $developer = Role::create([
            'name' => 'Developer',
            'type' => 'Developer',
            'color' => '#1d4ed8',
            'description' => 'Full access to everything',
            'created_by' => 1,
            'updated_by' => 1
        ]);
        $developer->permissions()->sync($fullAccess());

        // Admin (full access)
        $admin = Role::create([
            'name' => 'Admin',
            'type' => 'Admin',
            'color' => '#2563eb',
            'description' => 'Full access like Developer',
            'created_by' => 1,
            'updated_by' => 1
        ]);
        $admin->permissions()->sync($fullAccess());

        // Manager (restricted)
        $manager = Role::create([
            'name' => 'Manager',
            'type' => 'Manager',
            'color' => '#f59e0b',
            'description' => 'Manager with restricted access',
            'created_by' => 1,
            'updated_by' => 1
        ]);

        $restrictedForManager = [
            "Create Users",
            "Edit Users",
            "Delete Users",
            "Delete Groups",
            "View Roles",
            "Create Roles",
            "Edit Roles",
            "Delete Roles",
            "View Permissions",
            "Assign Permissions",
            "View Assigned Roles",
            "Delete Tags",
            "Delete Categories",
            "View Logs",
            "View Version Info"
        ];

        $managerPerms = Permission::whereNotIn('name', $restrictedForManager)->pluck('id');
        $manager->permissions()->sync($managerPerms);

        // Staff (very restricted)
        $staff = Role::create([
            'name' => 'Staff',
            'type' => 'Staff',
            'color' => '#10b981',
            'description' => 'Staff with minimal access',
            'created_by' => 1,
            'updated_by' => 1
        ]);

        $restrictedModulesForStaff = ["User Management", "Access Controls"];
        $restrictedPermsForStaff = [
            "Delete Files",
            "Edit Folders",
            "Delete Folders",
            "Create Folders",
            "Create Public Events",
            "Edit Public Events",
            "View Logs",
            "View Version Info",
            "View Categories",
            "Create Categories",
            "Edit Categories",
            "Delete Categories",
            "Delete Tags"
        ];

        $staffPerms = Permission::query()
            ->whereNotIn('module', $restrictedModulesForStaff)
            ->whereNotIn('name', $restrictedPermsForStaff)
            ->pluck('id')
            ->toArray();

        // Special case: in Lists of Users, allow only "View Users"
        $allowedExtra = Permission::where('module', 'Lists of Users')
            ->where('name', 'View Users')
            ->pluck('id')
            ->toArray();

        $staff->permissions()->sync(array_merge($staffPerms, $allowedExtra));
    }
}
