<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Add missing columns if they don't exist
            if (! Schema::hasColumn('tasks', 'actual_hours')) {
                $table->decimal('actual_hours', 8, 2)->nullable()->after('estimated_hours');
            }

            if (! Schema::hasColumn('tasks', 'started_at')) {
                $table->timestamp('started_at')->nullable()->after('due_date');
            }

            if (! Schema::hasColumn('tasks', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('started_at');
            }

            if (! Schema::hasColumn('tasks', 'reviewed_by')) {
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete()->after('updated_by');
            }

            if (! Schema::hasColumn('tasks', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $columns = ['actual_hours', 'started_at', 'completed_at', 'reviewed_by', 'reviewed_at'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('tasks', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
