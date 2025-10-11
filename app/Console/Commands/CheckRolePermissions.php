<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Role;
use App\Models\Permission;

class CheckRolePermissions extends Command
{
    protected $signature = 'check:role-permissions';
    protected $description = 'Check detailed role permission assignments';

    public function handle()
    {
        $roles = Role::with('permissions')->get();
        
        foreach($roles as $role) {
            $this->info("=== ROLE: {$role->name} ===");
            $this->info("Total permissions: " . $role->permissions->count());
            
            $permissions = $role->permissions;
            foreach($permissions as $permission) {
                $this->info("  - {$permission->name} (Module: {$permission->module})");
            }
            $this->info("");
        }
        
        // Test specific permissions for each role
        $testPermissions = ['Create Users', 'Delete Users', 'View Roles', 'Delete Files'];
        
        foreach($roles as $role) {
            $this->info("=== {$role->name} - Specific Permission Tests ===");
            foreach($testPermissions as $testPerm) {
                $hasPermission = $role->permissions()->get()->contains(function($permission) use ($testPerm) {
                    return $permission->name === $testPerm;
                });
                $this->info("  Can '{$testPerm}': " . ($hasPermission ? 'YES' : 'NO'));
            }
            $this->info("");
        }
    }
}