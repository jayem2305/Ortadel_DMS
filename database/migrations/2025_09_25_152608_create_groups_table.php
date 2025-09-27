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
            $table->string('logo')->nullable(); // path to image
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('groups');
    }
};
