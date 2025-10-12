<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestAuth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:auth';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test authentication and permission checking for admin user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Note: user_id is encrypted, so we need to check all users
        $user = \App\Models\User::all()->firstWhere('user_id', 'ADM_0001');
        
        if (!$user) {
            $this->error('Admin user not found!');
            return 1;
        }
        
        $this->info("Found user: {$user->user_id} - {$user->email}");
        $this->info("Role: " . ($user->role ? $user->role->name : 'No role'));
        
        // Test specific permissions
        $permissions = [
            'View Files',
            'Create Files',
            'View Folders',
            'Create Folders',
        ];
        
        $this->info("\nChecking permissions:");
        foreach ($permissions as $permissionName) {
            $has = $user->hasPermission($permissionName);
            $status = $has ? '✓' : '✗';
            $this->line("  {$status} {$permissionName}");
        }
        
        // Show all user permissions
        if ($user->role) {
            $this->info("\nAll permissions for role '{$user->role->name}':");
            $allPerms = $user->role->permissions()->get();
            foreach ($allPerms as $perm) {
                $this->line("  - {$perm->name} (Module: {$perm->module})");
            }
        }
        
        return 0;
    }
}
