<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->text('name'); // encrypted
            $table->text('description')->nullable(); // encrypted
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->text('assigned_color')->nullable(); // encrypted
            $table->longText('logo')->nullable(); // base64 encoded image or path
            $table->unsignedBigInteger('folder_id')->nullable();
            $table->boolean('is_default_group')->default(false);
            $table->boolean('workflow_participant')->default(false);
            $table->boolean('inherit_permissions')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('groups');
    }
};
