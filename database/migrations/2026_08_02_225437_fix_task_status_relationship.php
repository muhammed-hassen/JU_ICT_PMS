<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fix task_status_id relationship if missing
        Schema::table('tasks', function (Blueprint $table) {
            if (! Schema::hasColumn('tasks', 'sort_order')) {
                $table->unsignedInteger('sort_order')->default(0)->after('title');
            }

            // Add missing columns if they don't exist
            if (! Schema::hasColumn('tasks', 'task_status_id')) {
                $table->foreignId('task_status_id')->nullable()->constrained('task_statuses')->nullOnDelete()->after('phase_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (Schema::hasColumn('tasks', 'sort_order')) {
                $table->dropColumn('sort_order');
            }
        });
    }
};
