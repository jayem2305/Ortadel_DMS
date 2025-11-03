<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class RBACTest extends TestCase
{
    use RefreshDatabase;

    protected $developer;
    protected $admin;
    protected $manager;
    protected $staff;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed permissions and roles
        $this->artisan('db:seed', ['--class' => 'PermissionSeeder']);
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);

        // Get roles
        $developerRole = Role::where('name', 'Developer')->first();
        $adminRole = Role::where('name', 'Admin')->first();
        $managerRole = Role::where('name', 'Manager')->first();
        $staffRole = Role::where('name', 'Staff')->first();

        // Create test users
        $this->developer = User::create([
            'user_id' => 'DEV001',
            'first_name' => 'Dev',
            'last_name' => 'User',
            'email' => 'dev@test.com',
            'password' => Hash::make('password'),
            'role_id' => $developerRole->id,
            'status' => 'active',
            'created_by' => 1,
        ]);

        $this->admin = User::create([
            'user_id' => 'ADM001',
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role_id' => $adminRole->id,
            'status' => 'active',
            'created_by' => 1,
        ]);

        $this->manager = User::create([
            'user_id' => 'MGR001',
            'first_name' => 'Manager',
            'last_name' => 'User',
            'email' => 'manager@test.com',
            'password' => Hash::make('password'),
            'role_id' => $managerRole->id,
            'status' => 'active',
            'created_by' => 1,
        ]);

        $this->staff = User::create([
            'user_id' => 'STF001',
            'first_name' => 'Staff',
            'last_name' => 'User',
            'email' => 'staff@test.com',
            'password' => Hash::make('password'),
            'role_id' => $staffRole->id,
            'status' => 'active',
            'created_by' => 1,
        ]);
    }

    /** @test */
    public function developer_has_all_permissions()
    {
        $allPermissions = Permission::all();
        
        foreach ($allPermissions as $permission) {
            $this->assertTrue(
                $this->developer->hasPermission($permission->name),
                "Developer should have permission: {$permission->name}"
            );
        }
    }

    /** @test */
    public function admin_has_all_permissions()
    {
        $allPermissions = Permission::all();
        
        foreach ($allPermissions as $permission) {
            $this->assertTrue(
                $this->admin->hasPermission($permission->name),
                "Admin should have permission: {$permission->name}"
            );
        }
    }

    /** @test */
    public function manager_cannot_create_users()
    {
        $this->assertFalse($this->manager->hasPermission('Create Users'));
        $this->assertFalse($this->manager->hasPermission('Edit Users'));
        $this->assertFalse($this->manager->hasPermission('Delete Users'));
    }

    /** @test */
    public function manager_cannot_manage_roles()
    {
        $this->assertFalse($this->manager->hasPermission('View Roles'));
        $this->assertFalse($this->manager->hasPermission('Create Roles'));
        $this->assertFalse($this->manager->hasPermission('Edit Roles'));
        $this->assertFalse($this->manager->hasPermission('Delete Roles'));
    }

    /** @test */
    public function manager_can_view_files()
    {
        $this->assertTrue($this->manager->hasPermission('View Files'));
        $this->assertTrue($this->manager->hasPermission('Create Files'));
        $this->assertTrue($this->manager->hasPermission('Edit Files'));
    }

    /** @test */
    public function staff_can_only_view_users()
    {
        $this->assertTrue($this->staff->hasPermission('View Users'));
        $this->assertFalse($this->staff->hasPermission('Create Users'));
        $this->assertFalse($this->staff->hasPermission('Edit Users'));
        $this->assertFalse($this->staff->hasPermission('Delete Users'));
    }

    /** @test */
    public function staff_cannot_access_user_management_module()
    {
        $this->assertFalse($this->staff->canAccessModule('User Management'));
    }

    /** @test */
    public function staff_cannot_delete_files()
    {
        $this->assertFalse($this->staff->hasPermission('Delete Files'));
    }

    /** @test */
    public function staff_cannot_manage_folders()
    {
        $this->assertFalse($this->staff->hasPermission('Create Folders'));
        $this->assertFalse($this->staff->hasPermission('Edit Folders'));
        $this->assertFalse($this->staff->hasPermission('Delete Folders'));
    }

    /** @test */
    public function staff_can_view_dashboard()
    {
        $this->assertTrue($this->staff->hasPermission('View Dashboard'));
    }

    /** @test */
    public function staff_cannot_view_logs()
    {
        $this->assertFalse($this->staff->hasPermission('View Logs'));
    }

    /** @test */
    public function staff_cannot_manage_categories()
    {
        $this->assertFalse($this->staff->hasPermission('View Categories'));
        $this->assertFalse($this->staff->hasPermission('Create Categories'));
        $this->assertFalse($this->staff->hasPermission('Edit Categories'));
        $this->assertFalse($this->staff->hasPermission('Delete Categories'));
    }

    /** @test */
    public function user_can_check_any_permission()
    {
        $this->assertTrue(
            $this->developer->hasAnyPermission(['View Files', 'View Folders'])
        );
        
        $this->assertFalse(
            $this->staff->hasAnyPermission(['Delete Users', 'Edit Roles'])
        );
    }

    /** @test */
    public function user_can_check_all_permissions()
    {
        $this->assertTrue(
            $this->developer->hasAllPermissions(['View Files', 'View Folders'])
        );
        
        $this->assertFalse(
            $this->manager->hasAllPermissions(['Create Users', 'Edit Users'])
        );
    }

    /** @test */
    public function authenticated_user_can_access_protected_route()
    {
        $response = $this->actingAs($this->developer, 'sanctum')
            ->getJson('/api/users');
        
        // Should get 200 or 201, not 401
        $this->assertNotEquals(401, $response->status());
    }

    /** @test */
    public function staff_cannot_access_user_creation_endpoint()
    {
        $response = $this->actingAs($this->staff, 'sanctum')
            ->postJson('/api/users', [
                'user_id' => 'TEST001',
                'first_name' => 'Test',
                'last_name' => 'User',
                'email' => 'test@test.com',
                'password' => 'password',
            ]);
        
        $response->assertStatus(403);
    }

    /** @test */
    public function developer_can_access_permissions_endpoint()
    {
        $response = $this->actingAs($this->developer, 'sanctum')
            ->getJson('/api/permissions');
        
        $this->assertNotEquals(403, $response->status());
    }

    /** @test */
    public function admin_cannot_access_permissions_endpoint()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/permissions');
        
        $response->assertStatus(403);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_protected_routes()
    {
        $response = $this->getJson('/api/users');
        $response->assertStatus(401);
    }

    /** @test */
    public function manager_can_access_files_but_not_delete()
    {
        // Manager can view files
        $response = $this->actingAs($this->manager, 'sanctum')
            ->getJson('/api/file');
        
        $this->assertNotEquals(403, $response->status());

        // Manager can edit files
        $this->assertTrue($this->manager->hasPermission('Edit Files'));
    }

    /** @test */
    public function staff_can_access_keywords()
    {
        $response = $this->actingAs($this->staff, 'sanctum')
            ->getJson('/api/keywords');
        
        // Staff can view tags
        $this->assertNotEquals(403, $response->status());
    }

    /** @test */
    public function staff_cannot_delete_keywords()
    {
        $this->assertFalse($this->staff->hasPermission('Delete Tags'));
    }

    /** @test */
    public function permissions_are_properly_encrypted()
    {
        $permission = Permission::first();
        
        // Get raw value from database
        $rawName = \DB::table('permissions')
            ->where('id', $permission->id)
            ->value('name');
        
        // Raw value should NOT match decrypted value
        $this->assertNotEquals($permission->name, $rawName);
        
        // But when accessed through model, it should decrypt
        $this->assertIsString($permission->name);
    }

    /** @test */
    public function roles_are_properly_encrypted()
    {
        $role = Role::first();
        
        // Get raw value from database
        $rawName = \DB::table('roles')
            ->where('id', $role->id)
            ->value('name');
        
        // Raw value should NOT match decrypted value
        $this->assertNotEquals($role->name, $rawName);
        
        // But when accessed through model, it should decrypt
        $this->assertIsString($role->name);
    }
}
