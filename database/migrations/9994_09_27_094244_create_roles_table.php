<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->text('name');          // encrypted, must be text
            $table->text('type');          // encrypted, must be text
            $table->text('color')->default('#2563eb'); // encrypted hex code
            $table->text('description')->nullable();   // encrypted text
            $table->text('scope')->default('global'); // encrypted field
            $table->boolean('assign_to_groups')->default(true);
            $table->boolean('assign_to_users')->default(true);
            
            // TEMPORARILY using unsignedBigInteger to avoid dependency issues
            // Foreign keys will be added later after users table is created
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->text('module');        // encrypted
            $table->text('name');          // encrypted
            $table->text('description')->nullable(); // encrypted
            $table->timestamps();
        });

        Schema::create('role_permission', function (Blueprint $table) {
            $table->id();
            
            // Standard foreign keys for the many-to-many relationship
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();

            // TEMPORARILY using unsignedBigInteger to avoid dependency issues
            // Foreign keys will be added later after users table is created
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permission');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
