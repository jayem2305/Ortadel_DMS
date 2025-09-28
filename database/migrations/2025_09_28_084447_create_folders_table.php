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
            $table->foreignId('parent_id')->nullable()->constrained('folders')->onDelete('cascade');
            $table->json('access_users')->nullable();   // store IDs, not encrypted
            $table->json('access_groups')->nullable();  // store IDs, not encrypted
            $table->json('access_roles')->nullable();   // store IDs, not encrypted
            $table->foreignId('created_by')->constrained('users')->onDelete('set null')->nullable();
            $table->foreignId('updated_by')->constrained('users')->onDelete('set null')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('folders');
    }
};
