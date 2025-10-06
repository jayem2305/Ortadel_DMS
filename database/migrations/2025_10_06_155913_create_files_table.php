<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id(); // Primary key

            // Encrypted string fields - length increased to accommodate encryption
            $table->string('name', 512);                     // encrypted
            $table->text('description')->nullable();         // encrypted
            $table->date('expiration_date')->nullable();    // encrypted
            $table->string('owner_name', 512);              // encrypted

            // Folder foreign key - still nullable
            $table->foreignId('folder_id')
                ->nullable()
                ->constrained('folders')
                ->onDelete('set null'); // encrypted

            // JSON fields for reviewers/approvers
            $table->json('assign_reviewer')->nullable();   // encrypted
            $table->json('assign_approver')->nullable();   // encrypted

            $table->boolean('locked')->default(0);         // optional, unencrypted

            // File metadata - length increased for encrypted values
            $table->string('keywords', 512)->nullable();   // encrypted
            $table->string('org_filename', 512)->nullable(); // encrypted
            $table->string('file', 512)->nullable();       // encrypted
            $table->string('file_type', 512)->nullable();  // encrypted
            $table->integer('file_size')->nullable();      // encrypted

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
