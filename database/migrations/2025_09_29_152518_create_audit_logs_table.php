<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('module')->nullable(); // Module where action occurred
            $table->string('action'); // short string like 'created', 'updated', etc.
            $table->unsignedBigInteger('target_user_id')->nullable();
            $table->text('description')->nullable(); // JSON/text
            $table->unsignedBigInteger('performed_by'); // plain integer
            $table->timestamp('performed_at')->useCurrent();
            $table->timestamps();

            // Remove foreign key constraints, will add later
            //$table->foreign('target_user_id')->references('id')->on('users')->onDelete('set null');
            //$table->foreign('performed_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
