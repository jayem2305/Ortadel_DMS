<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('supporting_files', function (Blueprint $table) {
            $table->id(); // Primary key

            // Encrypted string fields - length increased to accommodate encryption
            $table->string('name', 512);                     // encrypted
            $table->text('description')->nullable();         // encrypted
            $table->date('expiration_date')->nullable();    // encrypted
            $table->string('owner_name', 512);              // encrypted

            // Folder foreign key - remove constraint, will add later
            $table->unsignedBigInteger('folder_id')->nullable(); // encrypted

            // JSON fields for reviewers/approvers
            $table->text('assign_reviewer')->nullable();   // changed from json to text
            $table->text('assign_approver')->nullable();   // encrypted

            $table->boolean('locked')->default(0);         // optional, unencrypted

            // File metadata - length increased for encrypted values
            $table->string('keywords', 512)->nullable();   // encrypted
            $table->string('category', 512)->nullable();   // encrypted
            $table->string('org_filename', 512)->nullable(); // encrypted
            $table->string('file', 512)->nullable();       // encrypted
            $table->string('file_type', 512)->nullable();  // encrypted
            $table->integer('file_size')->nullable();      // encrypted
            $table->integer('version')->default(1);// encrypted
            $table->integer('file_id')->nullable();// encrypted
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->string('status', 50)->default('Released'); // new status field
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
