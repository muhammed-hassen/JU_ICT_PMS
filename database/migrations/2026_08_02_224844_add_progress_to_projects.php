<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (! Schema::hasColumn('projects', 'progress_percentage')) {
                $table->decimal('progress_percentage', 5, 2)->default(0)->after('status');
            }
        });

        Schema::table('tasks', function (Blueprint $table) {
            if (! Schema::hasColumn('tasks', 'due_date')) {
                $table->date('due_date')->nullable()->after('estimated_hours');
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'progress_percentage')) {
                $table->dropColumn('progress_percentage');
            }
        });

        Schema::table('tasks', function (Blueprint $table) {
            if (Schema::hasColumn('tasks', 'due_date')) {
                $table->dropColumn('due_date');
            }
        });
    }
};
