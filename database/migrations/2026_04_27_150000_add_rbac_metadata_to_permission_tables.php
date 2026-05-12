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
        $tableNames = config('permission.table_names');

        Schema::table($tableNames['permissions'], static function (Blueprint $table) {
            $table->string('module', 50)->nullable()->after('guard_name');
            $table->text('description')->nullable()->after('module');
            $table->index('module');
        });

        Schema::table($tableNames['roles'], static function (Blueprint $table) {
            $table->text('description')->nullable()->after('guard_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');

        Schema::table($tableNames['roles'], static function (Blueprint $table) {
            $table->dropColumn('description');
        });

        Schema::table($tableNames['permissions'], static function (Blueprint $table) {
            $table->dropIndex(['module']);
            $table->dropColumn(['module', 'description']);
        });
    }
};
