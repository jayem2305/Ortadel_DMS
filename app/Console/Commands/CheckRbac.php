<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;

class CheckRbac extends Command
{
    protected $signature = 'rbac:check';
    protected $description = 'Check RBAC system status';

    public function handle()
    {
        $this->info('=== RBAC SYSTEM STATUS ===');
        
        $userCount = User::count();
        $roleCount = Role::count();
        $permissionCount = Permission::count();
        
        $this->info("Total users: {$userCount}");
        $this->info("Total roles: {$roleCount}");
        $this->info("Total permissions: {$permissionCount}");
        
        $this->info("\n=== CHECKING ROLE-PERMISSION PIVOT TABLE ===");
        $rolePermissionCount = \DB::table('role_permission')->count();
        $this->info("Total role-permission relationships: {$rolePermissionCount}");
        
        $this->info("\n=== DETAILED PERMISSION ANALYSIS ===");
        $permissions = Permission::all();
        $this->info("First 10 permissions with decrypted names:");
        foreach($permissions->take(10) as $perm) {
            $this->info("  - {$perm->name} (Module: {$perm->module})");
        }
        
        $this->info("\n=== ROLE-PERMISSION BREAKDOWN ===");
        $roles = Role::all();
        foreach($roles as $role) {
            $permissionIds = $role->permissions()->pluck('permissions.id')->toArray();
            $permCount = count($permissionIds);
            $this->info("Role: {$role->name} (ID: {$role->id}) has {$permCount} permissions");
            
            if ($permCount > 0) {
                $this->info("  Permission IDs: " . implode(', ', array_slice($permissionIds, 0, 10)) . 
                    ($permCount > 10 ? " ... and " . ($permCount - 10) . " more" : ""));
            }
        }
        
        $this->info("\n=== USERS AND THEIR ACTUAL PERMISSIONS ===");
        $users = User::with('role')->get();
        foreach($users as $user) {
            $roleName = $user->role ? $user->role->name : 'No role assigned';
            $this->info("User: {$user->first_name} {$user->last_name} ({$user->email})");
            $this->info("Role: {$roleName} (ID: {$user->role_id})");
            
            if ($user->role) {
                $permissions = $user->role->permissions()->take(5)->get();
                $this->info("  Sample permissions:");
                foreach($permissions as $perm) {
                    $this->info("    - {$perm->name}");
                }
                
                // Test permission checking
                $testPermissions = ['View Dashboard', 'Create Users', 'Delete Users'];
                foreach($testPermissions as $testPerm) {
                    $hasPermission = $user->hasPermission($testPerm);
                    $this->info("    Can '{$testPerm}': " . ($hasPermission ? 'YES' : 'NO'));
                }
            }
            $this->info("");
        }
    }
}