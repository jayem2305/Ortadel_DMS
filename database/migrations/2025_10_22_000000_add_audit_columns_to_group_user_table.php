<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('group_user')) {
            Schema::table('group_user', function (Blueprint $table) {
                if (!Schema::hasColumn('group_user', 'created_by')) {
                    $table->unsignedBigInteger('created_by')->nullable()->after('updated_at');
                }
                if (!Schema::hasColumn('group_user', 'updated_by')) {
                    $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('group_user')) {
            Schema::table('group_user', function (Blueprint $table) {
                if (Schema::hasColumn('group_user', 'created_by')) {
                    $table->dropColumn('created_by');
                }
                if (Schema::hasColumn('group_user', 'updated_by')) {
                    $table->dropColumn('updated_by');
                }
            });
        }
    }
};
