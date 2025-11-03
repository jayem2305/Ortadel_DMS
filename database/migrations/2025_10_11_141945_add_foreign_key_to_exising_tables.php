<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Add Foreign Key Constraints to Existing Tables
 * 
 * PURPOSE: This migration adds foreign key constraints to tables that were initially created
 * with unsignedBigInteger columns to avoid circular dependency issues during fresh migration.
 * 
 * MIGRATION SEQUENCE PROBLEM:
 * - Tables like 'roles', 'permissions', 'folders' are created BEFORE 'users' table
 * - But they need to reference the 'users' table for created_by/updated_by columns
 * - Solution: Create tables with unsignedBigInteger first, then add foreign keys later
 * 
 * WHY THIS APPROACH:
 * 1. Avoids circular dependency during fresh migration
 * 2. Ensures proper migration order (tables before constraints)
 * 3. Maintains referential integrity after all tables are created
 * 4. Works for both fresh migrations and existing databases
 * 
 * TABLES AFFECTED:
 * - roles: created_by, updated_by → foreign keys to users
 * - permissions: created_by, updated_by → foreign keys to users (if columns exist)
 * - role_permission: created_by, updated_by → foreign keys to users (if columns exist)
 * - folders: created_by, updated_by → foreign keys to users (if columns exist)
 * 
 * SAFETY FEATURES:
 * - Try-catch blocks prevent errors if constraints already exist
 * - Column existence checks ensure compatibility with different migration states
 * - Named constraints for easier identification and management
 */
return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * This method adds foreign key constraints to existing tables that were previously
     * using unsignedBigInteger columns for user references. Each operation is wrapped
     * in try-catch blocks to handle cases where constraints might already exist.
     */
    public function up(): void
    {
        // ROLES TABLE: Add foreign key constraints for audit trail
        // These constraints ensure that created_by and updated_by always reference valid users
        try {
            Schema::table('roles', function (Blueprint $table) {
                if (Schema::hasColumn('roles', 'created_by')) {
                    $table->foreign('created_by', 'roles_created_by_fk')->references('id')->on('users')->nullOnDelete();
                }
                if (Schema::hasColumn('roles', 'updated_by')) {
                    $table->foreign('updated_by', 'roles_updated_by_fk')->references('id')->on('users')->nullOnDelete();
                }
            });
        } catch (\Exception $e) {
            // Foreign key might already exist, skip gracefully
        }

        // PERMISSIONS TABLE: Add foreign key constraints if the columns exist
        // The permissions table structure varies depending on when migrations were run
        if (Schema::hasTable('permissions') && Schema::hasColumn('permissions', 'created_by')) {
            try {
                Schema::table('permissions', function (Blueprint $table) {
                    $table->foreign('created_by', 'permissions_created_by_fk')->references('id')->on('users')->nullOnDelete();
                    $table->foreign('updated_by', 'permissions_updated_by_fk')->references('id')->on('users')->nullOnDelete();
                });
            } catch (\Exception $e) {
                // Foreign key might already exist, skip gracefully
            }
        }

        // ROLE_PERMISSION TABLE: Add foreign key constraints for pivot table audit trail
        // This tracks who assigned/removed role-permission relationships
        if (Schema::hasTable('role_permission') && Schema::hasColumn('role_permission', 'created_by')) {
            try {
                Schema::table('role_permission', function (Blueprint $table) {
                    $table->foreign('created_by', 'role_permission_created_by_fk')->references('id')->on('users')->nullOnDelete();
                    $table->foreign('updated_by', 'role_permission_updated_by_fk')->references('id')->on('users')->nullOnDelete();
                });
            } catch (\Exception $e) {
                // Foreign key might already exist, skip gracefully
            }
        }

        // FOLDERS TABLE: Add foreign key constraints for document management audit trail
        // Critical for tracking folder creation and modifications in the DMS
        if (Schema::hasTable('folders') && Schema::hasColumn('folders', 'created_by')) {
            try {
                Schema::table('folders', function (Blueprint $table) {
                    $table->foreign('created_by', 'folders_created_by_fk')->references('id')->on('users')->nullOnDelete();
                    $table->foreign('updated_by', 'folders_updated_by_fk')->references('id')->on('users')->nullOnDelete();
                });
            } catch (\Exception $e) {
                // Foreign key might already exist, skip gracefully
            }
        }

        // USERS TABLE: Add foreign key constraints for user audit trail and role
        try {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'role_id')) {
                    $table->foreign('role_id', 'users_role_id_fk')->references('id')->on('roles')->cascadeOnDelete();
                }
                if (Schema::hasColumn('users', 'created_by')) {
                    $table->foreign('created_by', 'users_created_by_fk')->references('id')->on('users')->nullOnDelete();
                }
                if (Schema::hasColumn('users', 'last_updated_by')) {
                    $table->foreign('last_updated_by', 'users_last_updated_by_fk')->references('id')->on('users')->nullOnDelete();
                }
            });
        } catch (\Exception $e) {
            // Foreign key might already exist, skip gracefully
        }

        // SESSIONS TABLE: Add foreign key constraint to users
        try {
            Schema::table('sessions', function (Blueprint $table) {
                if (Schema::hasColumn('sessions', 'user_id')) {
                    $table->foreign('user_id', 'sessions_user_id_fk')->references('id')->on('users')->nullOnDelete();
                }
            });
        } catch (\Exception $e) {
            // Foreign key might already exist, skip gracefully
        }

        // FOLDERS TABLE: Add parent_id foreign key constraint
        try {
            Schema::table('folders', function (Blueprint $table) {
                if (Schema::hasColumn('folders', 'parent_id')) {
                    $table->foreign('parent_id', 'folders_parent_id_fk')->references('id')->on('folders')->cascadeOnDelete();
                }
            });
        } catch (\Exception $e) {
            // Foreign key might already exist, skip gracefully
        }

        // FILES TABLE: Add foreign key constraint to folders
        try {
            Schema::table('files', function (Blueprint $table) {
                if (Schema::hasColumn('files', 'folder_id')) {
                    $table->foreign('folder_id', 'files_folder_id_fk')->references('id')->on('folders')->nullOnDelete();
                }
            });
        } catch (\Exception $e) {
            // Foreign key might already exist, skip gracefully
        }

        // AUDIT_LOGS TABLE: Add foreign key constraints
        try {
            Schema::table('audit_logs', function (Blueprint $table) {
                if (Schema::hasColumn('audit_logs', 'target_user_id')) {
                    $table->foreign('target_user_id', 'audit_logs_target_user_id_fk')->references('id')->on('users')->nullOnDelete();
                }
                if (Schema::hasColumn('audit_logs', 'performed_by')) {
                    $table->foreign('performed_by', 'audit_logs_performed_by_fk')->references('id')->on('users')->cascadeOnDelete();
                }
            });
        } catch (\Exception $e) {
            // Foreign key might already exist, skip gracefully
        }
        // FILES TABLE: Add foreign key constraints
        try {
            Schema::table('files', function (Blueprint $table) {
                if (Schema::hasColumn('files', 'category_id')) {
                    $table->foreign('category_id', 'files_category_id_fk')->references('id')->on('categories')->nullOnDelete();
                }
            });
        } catch (\Exception $e) {
            // Foreign key might already exist, skip gracefully
        }
    }

    /**
     * Reverse the migrations.
     * 
     * This method removes the foreign key constraints that were added in the up() method.
     * This is important for rollback scenarios and ensures clean migration reversibility.
     * 
     * NOTE: After rolling back these constraints, the columns will revert to being
     * unsignedBigInteger fields without referential integrity enforcement.
     */
    public function down(): void
    {
        // Remove foreign key constraints from roles table
        Schema::table('roles', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
        });

        // Remove foreign key constraints from permissions table (if it exists)
        if (Schema::hasTable('permissions')) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->dropForeign(['created_by']);
                $table->dropForeign(['updated_by']);
            });
        }

        // Remove foreign key constraints from role_permission table (if it exists)
        if (Schema::hasTable('role_permission')) {
            Schema::table('role_permission', function (Blueprint $table) {
                $table->dropForeign(['created_by']);
                $table->dropForeign(['updated_by']);
            });
        }

        // Remove foreign key constraints from folders table (if it exists)
        if (Schema::hasTable('folders')) {
            Schema::table('folders', function (Blueprint $table) {
                $table->dropForeign(['created_by']);
                $table->dropForeign(['updated_by']);
                $table->dropForeign(['parent_id']);
            });
        }

        // Remove foreign key constraints from users table (if it exists)
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['role_id']);
                $table->dropForeign(['created_by']);
                $table->dropForeign(['last_updated_by']);
            });
        }

        // Remove foreign key constraints from sessions table (if it exists)
        if (Schema::hasTable('sessions')) {
            Schema::table('sessions', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });
        }

        // Remove foreign key constraints from files table (if it exists)
        if (Schema::hasTable('files')) {
            Schema::table('files', function (Blueprint $table) {
                $table->dropForeign(['folder_id']);
            });
        }

        // Remove foreign key constraints from audit_logs table (if it exists)
        if (Schema::hasTable('audit_logs')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->dropForeign(['target_user_id']);
                $table->dropForeign(['performed_by']);
            });
        }
        // Remove foreign key constraints from audit_logs table (if it exists)
        if (Schema::hasTable('files')) {
            Schema::table('files', function (Blueprint $table) {
                $table->dropForeign(['category_id']);
            });
        }
    }
};
