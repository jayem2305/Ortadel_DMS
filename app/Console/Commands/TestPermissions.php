<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Permission;
use App\Models\User;

class TestPermissions extends Command
{
    protected $signature = 'test:permissions';
    protected $description = 'Test permission decryption and checking';

    public function handle()
    {
        $this->info('=== TESTING PERMISSION DECRYPTION ===');
        
        // Test individual permission decryption
        $permissions = Permission::take(5)->get();
        foreach($permissions as $permission) {
            $this->info("Permission ID: {$permission->id}");
            $this->info("Raw name from DB: " . \DB::table('permissions')->where('id', $permission->id)->value('name'));
            $this->info("Decrypted name: {$permission->name}");
            $this->info("Module: {$permission->module}");
            $this->info("---");
        }
        
        $this->info("\n=== TESTING USER PERMISSION CHECKING ===");
        
        $user = User::first();
        if ($user && $user->role) {
            $this->info("Testing user: {$user->first_name} {$user->last_name}");
            $this->info("Role: {$user->role->name}");
            
            // Test with actual permission names from database
            $firstPermission = $user->role->permissions()->first();
            if ($firstPermission) {
                $this->info("First permission name: '{$firstPermission->name}'");
                $hasPermission = $user->hasPermission($firstPermission->name);
                $this->info("User has this permission: " . ($hasPermission ? 'YES' : 'NO'));
                
                // Test basic permissions
                $testPermissions = ['View Dashboard', 'Create Users', 'View Files'];
                foreach($testPermissions as $testPerm) {
                    $result = $user->hasPermission($testPerm);
                    $this->info("Can '{$testPerm}': " . ($result ? 'YES' : 'NO'));
                }
            }
        }
    }
}