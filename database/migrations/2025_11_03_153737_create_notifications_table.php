<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // document_upload, document_update, document_delete, etc.
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // Additional data like document_id, user_id, etc.
            $table->boolean('read')->default(false);
            $table->boolean('email_sent')->default(false);
            $table->string('priority')->default('normal'); // low, normal, high
            $table->string('action_url')->nullable(); // URL to navigate to
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
