<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            [
                'name' => 'Dashboard',
                'permissions' => [
                    ['name' => 'View Dashboard', 'description' => 'Access and view the Dashboard module.']
                ],
            ],
            [
                'name' => 'Document Management',
                'permissions' => [
                    ['name' => 'View Files', 'description' => 'View all files in Document Management.'],
                    ['name' => 'Create Files', 'description' => 'Create new documents.'],
                    ['name' => 'Edit Files', 'description' => 'Edit existing documents.'],
                    ['name' => 'Delete Files', 'description' => 'Delete documents permanently.'],
                    ['name' => 'View Folders', 'description' => 'View all folders.'],
                    ['name' => 'Create Folders', 'description' => 'Create new folders.'],
                    ['name' => 'Edit Folders', 'description' => 'Edit existing folders.'],
                    ['name' => 'Delete Folders', 'description' => 'Delete folders permanently.'],
                    ['name' => 'View File Versions', 'description' => 'View document version history.'],
                    ['name' => 'Restore File Versions', 'description' => 'Restore previous versions of documents.'],
                    ['name' => 'Delete File Versions', 'description' => 'Delete specific versions of documents.'],
                    ['name' => 'View Shared Links', 'description' => 'View all shared links for documents.'],
                    ['name' => 'Create Shared Links', 'description' => 'Create new shared links for documents.'],
                    ['name' => 'Delete Shared Links', 'description' => 'Delete shared links permanently.'],
                    ['name' => 'View Favorites', 'description' => 'View favorite documents and folders.'],
                    ['name' => 'Add to Favorites', 'description' => 'Add documents or folders to favorites.'],
                    ['name' => 'Remove from Favorites', 'description' => 'Remove documents or folders from favorites.'],
                    ['name' => 'View Recent Files', 'description' => 'View recently accessed files.'],
                    ['name' => 'View Shared With Me', 'description' => 'View documents shared with the user.'],
                    ['name' => 'Upload Files', 'description' => 'Upload new files to the system.'],
                    ['name' => 'Download Files', 'description' => 'Download files from the system.'],
                    ['name' => 'Move Files', 'description' => 'Move files between folders.'],
                    ['name' => 'Copy Files', 'description' => 'Copy files within the system.'],
                    ['name' => 'View Comments', 'description' => 'View comments on documents.'],
                    ['name' => 'Add Comments', 'description' => 'Add comments to documents.'],
                    ['name' => 'Edit Comments', 'description' => 'Edit comments on documents.'],
                    ['name' => 'Delete Comments', 'description' => 'Delete comments from documents.'],
                    ['name' => 'Approve Files', 'description' => 'Approve files submitted for review.'],
                    ['name' => 'Review Files', 'description' => 'Review files and provide feedback.'],
                ],
            ],
            [
                'name' => 'Lists of Users',
                'permissions' => [
                    ['name' => 'View Users', 'description' => 'View all user accounts.'],
                    ['name' => 'Create Users', 'description' => 'Create new users.'],
                    ['name' => 'Edit Users', 'description' => 'Edit user accounts.'],
                    ['name' => 'Delete Users', 'description' => 'Delete user accounts permanently.'],
                ],
            ],
            [
                'name' => 'User Management',
                'permissions' => [
                    ['name' => 'View Groups', 'description' => 'View all user groups.'],
                    ['name' => 'Create Groups', 'description' => 'Create new user groups.'],
                    ['name' => 'Edit Groups', 'description' => 'Edit existing groups.'],
                    ['name' => 'Delete Groups', 'description' => 'Delete groups permanently.'],
                    ['name' => 'View Roles', 'description' => 'View all roles.'],
                    ['name' => 'Create Roles', 'description' => 'Create new roles.'],
                    ['name' => 'Edit Roles', 'description' => 'Edit roles.'],
                    ['name' => 'Delete Roles', 'description' => 'Delete roles permanently.'],
                    ['name' => 'View Permissions', 'description' => 'View all permissions.'],
                    ['name' => 'Assign Permissions', 'description' => 'Assign permissions to roles.'],

                ],
            ],
            [
                'name' => 'Access Controls',
                'permissions' => [
                    ['name' => 'View Assigned Groups', 'description' => 'View which groups have access.'],
                    ['name' => 'View Assigned Roles', 'description' => 'View which roles have access.'],
                ],
            ],
            [
                'name' => 'Tags',
                'permissions' => [
                    ['name' => 'View Tags', 'description' => 'View all tags.'],
                    ['name' => 'Create Tags', 'description' => 'Create new tags.'],
                    ['name' => 'Edit Tags', 'description' => 'Edit existing tags.'],
                    ['name' => 'Delete Tags', 'description' => 'Delete tags permanently.'],
                    ['name' => 'View Categories', 'description' => 'View tag categories.'],
                    ['name' => 'Create Categories', 'description' => 'Create new tag categories.'],
                    ['name' => 'Edit Categories', 'description' => 'Edit tag categories.'],
                    ['name' => 'Delete Categories', 'description' => 'Delete tag categories permanently.'],

                ],
            ],
            [
                'name' => 'Calendar',
                'permissions' => [
                    ['name' => 'View Calendar', 'description' => 'View all calendar events.'],
                    ['name' => 'Create Events', 'description' => 'Create new calendar events.'],
                    ['name' => 'Create Public Events', 'description' => 'Create new calendar Public events.'],
                    ['name' => 'Edit Public Events', 'description' => 'Edit existing calendar Public events.'],
                    ['name' => 'Edit Events', 'description' => 'Edit existing calendar events.'],
                    ['name' => 'Delete Events', 'description' => 'Delete calendar events permanently.'],
                    ['name' => 'Move Events', 'description' => 'Move events between different dates or times.'],
                    ['name' => 'Delete Public Events', 'description' => 'Delete Public calendar events permanently.'],
                ],
            ],
            [
                'name' => 'Logs / Audit',
                'permissions' => [
                    ['name' => 'View Logs', 'description' => 'View audit logs for the system.'],
                ],
            ],
            [
                'name' => 'Recycle Bin',
                'permissions' => [
                    ['name' => 'View Recycle Bin', 'description' => 'View deleted items.'],
                    ['name' => 'Restore', 'description' => 'Restore deleted items.'],
                    ['name' => 'Empty Recycle Bin', 'description' => 'Permanently delete all items in the Recycle Bin.'],
                    ['name' => 'Delete Permanently', 'description' => 'Permanently delete specific items from the Recycle Bin.'],
                ],
            ],
            [
                'name' => 'Settings',
                'permissions' => [
                    ['name' => 'View Profiles', 'description' => 'View user profiles.'],
                    ['name' => 'View User Logs', 'description' => 'View individual user activity logs.'],
                    ['name' => 'View Version Info', 'description' => 'View system version information.'],
                    ['name' => 'Edit Settings', 'description' => 'Modify system settings.'],
                ],
            ],
        ];

        foreach ($modules as $module) {
            foreach ($module['permissions'] as $perm) {
                Permission::updateOrCreate(
                    [
                        'name' => $perm['name'], // Remove encryption - model will handle it
                        'module' => $module['name'], // Remove encryption - model will handle it
                    ],
                    [
                        'description' => $perm['description'],
                        'created_by' => 1, // admin ID or Auth::id()
                        'updated_by' => 1,
                    ]
                );
            }
        }
    }
}
