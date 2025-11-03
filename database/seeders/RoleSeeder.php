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

        // Get all permissions and filter by decrypted names
        $allPermissions = Permission::all();
        $managerPermIds = [];
        foreach ($allPermissions as $permission) {
            if (!in_array($permission->name, $restrictedForManager)) {
                $managerPermIds[] = $permission->id;
            }
        }
        $manager->permissions()->sync($managerPermIds);

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
            "Create Users",  // Add this explicitly
            "Edit Users",    // Add this explicitly  
            "Delete Users",  // Add this explicitly
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


        // Get all permissions and filter by decrypted names and modules
        $staffPermIds = [];
        foreach ($allPermissions as $permission) {
            // Skip if module is restricted
            if (in_array($permission->module, $restrictedModulesForStaff)) {
                continue;
            }
            // Skip if permission name is restricted
            if (in_array($permission->name, $restrictedPermsForStaff)) {
                continue;
            }
            $staffPermIds[] = $permission->id;
        }

        // Special case: in Lists of Users, allow only "View Users"
        foreach ($allPermissions as $permission) {
            if ($permission->module === 'Lists of Users' && $permission->name === 'View Users') {
                $staffPermIds[] = $permission->id;
                break;
            }
        }

        $staff->permissions()->sync(array_unique($staffPermIds));

        // Reviewer (document review access - read-only + comments + review)
        $reviewer = Role::create([
            'name' => 'Reviewer',
            'type' => 'Reviewer',
            'color' => '#8b5cf6',
            'description' => 'Can review and comment on documents',
            'created_by' => 1,
            'updated_by' => 1
        ]);

        $reviewerAllowedPerms = [
            "View Dashboard",
            "View Users",
            "View Groups",
            "View Roles",
            "View Files",
            "View Folders",
            "View File Versions",
            "View Shared Links",
            "View Favorites",
            "Add to Favorites",
            "Remove from Favorites",
            "View Recent Files",
            "View Shared With Me",
            "Download Files",
            "View Categories",
            "View Tags",
            "View Calendar",
            "View Comments",
            "Add Comments",
            "Edit Comments",
            "Review Files"
        ];

        $reviewerPermIds = [];
        foreach ($allPermissions as $permission) {
            if (in_array($permission->name, $reviewerAllowedPerms)) {
                $reviewerPermIds[] = $permission->id;
            }
        }
        $reviewer->permissions()->sync($reviewerPermIds);

        // Approver (document approval access - read-only + approve + comments)
        $approver = Role::create([
            'name' => 'Approver',
            'type' => 'Approver',
            'color' => '#ec4899',
            'description' => 'Can approve and reject documents',
            'created_by' => 1,
            'updated_by' => 1
        ]);

        $approverAllowedPerms = [
            "View Dashboard",
            "View Users",
            "View Groups",
            "View Roles",
            "View Files",
            "View Folders",
            "View File Versions",
            "View Shared Links",
            "View Favorites",
            "Add to Favorites",
            "Remove from Favorites",
            "View Recent Files",
            "View Shared With Me",
            "Download Files",
            "View Categories",
            "View Tags",
            "View Calendar",
            "View Comments",
            "Add Comments",
            "Approve Files",
            "Review Files"
        ];

        $approverPermIds = [];
        foreach ($allPermissions as $permission) {
            if (in_array($permission->name, $approverAllowedPerms)) {
                $approverPermIds[] = $permission->id;
            }
        }
        $approver->permissions()->sync($approverPermIds);
    }
}
