<?php

namespace App\Services;

use App\Models\User;
use App\Models\Permission;

class PermissionService
{
    /**
     * Get user permissions for frontend consumption
     */
    public static function getUserPermissions(User $user): array
    {
        if (!$user->role) {
            return [];
        }

        // Get permissions and let the model handle decryption
        return $user->role->permissions()
            ->get()
            ->pluck('name')
            ->toArray();
    }

    /**
     * Get permissions grouped by module for frontend
     */
    public static function getUserPermissionsByModule(User $user): array
    {
        if (!$user->role) {
            return [];
        }

        // Get permissions and let the model handle decryption
        $permissions = $user->role->permissions()
            ->get();

        $grouped = [];
        foreach ($permissions as $permission) {
            $module = $permission->module; // Will be decrypted automatically
            if (!isset($grouped[$module])) {
                $grouped[$module] = [];
            }
            $grouped[$module][] = $permission->name; // Will be decrypted automatically
        }

        return $grouped;
    }

    /**
     * Check if user can perform CRUD operations on a module
     */
    public static function getModulePermissions(User $user, string $module): array
    {
        $permissions = self::getUserPermissions($user);
        
        return [
            'can_view' => in_array("View {$module}", $permissions),
            'can_create' => in_array("Create {$module}", $permissions),
            'can_edit' => in_array("Edit {$module}", $permissions),
            'can_delete' => in_array("Delete {$module}", $permissions),
        ];
    }

    /**
     * Check if a user has a specific permission (backward compatibility)
     */
    public function hasPermission(User $user, string $module, string $permission): bool
    {
        $permissions = self::getUserPermissions($user);
        return in_array($permission, $permissions) || in_array("{$permission} {$module}", $permissions);
    }

    /**
     * Check if user has any permission in a module
     */
    public function hasModuleAccess(User $user, string $module): bool
    {
        $permissions = self::getUserPermissionsByModule($user);
        return isset($permissions[$module]) && !empty($permissions[$module]);
    }

    /**
     * Generate permission constants for frontend
     */
    public static function getPermissionConstants(): array
    {
        return [
            // Dashboard
            'VIEW_DASHBOARD' => 'View Dashboard',
            
            // Document Management
            'VIEW_FILES' => 'View Files',
            'CREATE_FILES' => 'Create Files',
            'EDIT_FILES' => 'Edit Files',
            'DELETE_FILES' => 'Delete Files',
            'VIEW_FOLDERS' => 'View Folders',
            'CREATE_FOLDERS' => 'Create Folders',
            'EDIT_FOLDERS' => 'Edit Folders',
            'DELETE_FOLDERS' => 'Delete Folders',
            'VIEW_FILE_VERSIONS' => 'View File Versions',
            'RESTORE_FILE_VERSIONS' => 'Restore File Versions',
            'DELETE_FILE_VERSIONS' => 'Delete File Versions',
            'VIEW_SHARED_LINKS' => 'View Shared Links',
            'CREATE_SHARED_LINKS' => 'Create Shared Links',
            'DELETE_SHARED_LINKS' => 'Delete Shared Links',
            'VIEW_FAVORITES' => 'View Favorites',
            'ADD_TO_FAVORITES' => 'Add to Favorites',
            'REMOVE_FROM_FAVORITES' => 'Remove from Favorites',
            'VIEW_RECENT_FILES' => 'View Recent Files',
            'VIEW_SHARED_WITH_ME' => 'View Shared With Me',
            'UPLOAD_FILES' => 'Upload Files',
            'DOWNLOAD_FILES' => 'Download Files',
            'MOVE_FILES' => 'Move Files',
            'COPY_FILES' => 'Copy Files',
            
            // Lists of Users (User Management)
            'VIEW_USERS' => 'View Users',
            'CREATE_USERS' => 'Create Users',
            'EDIT_USERS' => 'Edit Users',
            'DELETE_USERS' => 'Delete Users',
            
            // User Management (Groups, Roles, Permissions)
            'VIEW_GROUPS' => 'View Groups',
            'CREATE_GROUPS' => 'Create Groups',
            'EDIT_GROUPS' => 'Edit Groups',
            'DELETE_GROUPS' => 'Delete Groups',
            'VIEW_ROLES' => 'View Roles',
            'CREATE_ROLES' => 'Create Roles',
            'EDIT_ROLES' => 'Edit Roles',
            'DELETE_ROLES' => 'Delete Roles',
            'VIEW_PERMISSIONS' => 'View Permissions',
            'ASSIGN_PERMISSIONS' => 'Assign Permissions',
            
            // Access Controls
            'VIEW_ASSIGNED_GROUPS' => 'View Assigned Groups',
            'VIEW_ASSIGNED_ROLES' => 'View Assigned Roles',
            
            // Tags
            'VIEW_TAGS' => 'View Tags',
            'CREATE_TAGS' => 'Create Tags',
            'EDIT_TAGS' => 'Edit Tags',
            'DELETE_TAGS' => 'Delete Tags',
            'VIEW_CATEGORIES' => 'View Categories',
            'CREATE_CATEGORIES' => 'Create Categories',
            'EDIT_CATEGORIES' => 'Edit Categories',
            'DELETE_CATEGORIES' => 'Delete Categories',
            
            // Calendar
            'VIEW_CALENDAR' => 'View Calendar',
            'CREATE_EVENTS' => 'Create Events',
            'CREATE_PUBLIC_EVENTS' => 'Create Public Events',
            'EDIT_PUBLIC_EVENTS' => 'Edit Public Events',
            'EDIT_EVENTS' => 'Edit Events',
            'DELETE_EVENTS' => 'Delete Events',
            'MOVE_EVENTS' => 'Move Events',
            'DELETE_PUBLIC_EVENTS' => 'Delete Public Events',
            
            // Logs & Audit
            'VIEW_LOGS' => 'View Logs',
            
            // Recycle Bin
            'VIEW_RECYCLE_BIN' => 'View Recycle Bin',
            'RESTORE' => 'Restore',
            'EMPTY_RECYCLE_BIN' => 'Empty Recycle Bin',
            'DELETE_PERMANENTLY' => 'Delete Permanently',
            
            // Settings
            'VIEW_PROFILES' => 'View Profiles',
            'VIEW_USER_LOGS' => 'View User Logs',
            'VIEW_VERSION_INFO' => 'View Version Info',
            'EDIT_SETTINGS' => 'Edit Settings',
        ];
    }
}