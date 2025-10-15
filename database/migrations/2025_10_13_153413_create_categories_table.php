<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->text('name'); // Encrypted
            $table->text('description')->nullable(); // Encrypted
            $table->enum('status', ['active', 'inactive'])->default('active');

            // Audit fields
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();

            // Not encrypted
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
