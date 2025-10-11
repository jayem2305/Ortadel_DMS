<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('folders', function (Blueprint $table) {
            $table->id();
            $table->text('name'); // encrypted
            $table->text('description')->nullable(); // encrypted
            $table->unsignedBigInteger('parent_id')->nullable(); // Remove foreign key constraint, will add later
            $table->json('access_users')->nullable();   // store IDs, not encrypted
            $table->json('access_groups')->nullable();  // store IDs, not encrypted
            $table->json('access_roles')->nullable();   // store IDs, not encrypted

            // TEMPORARILY using unsignedBigInteger to avoid dependency issues
            // Foreign keys will be added later after users table is created
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('folders');
    }
};
