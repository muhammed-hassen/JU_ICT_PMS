<?php

// database/migrations/2026_08_08_000000_create_task_status_history_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_status_id')->nullable()->constrained('task_statuses')->nullOnDelete();
            $table->foreignId('to_status_id')->constrained('task_statuses')->cascadeOnDelete();
            $table->foreignId('changed_by')->constrained('users')->cascadeOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['task_id', 'created_at']);
        });

        // Add columns to tasks table
        Schema::table('tasks', function (Blueprint $table) {
            if (! Schema::hasColumn('tasks', 'status_changed_at')) {
                $table->timestamp('status_changed_at')->nullable()->after('status_updated_at');
            }
            if (! Schema::hasColumn('tasks', 'time_in_status')) {
                $table->json('time_in_status')->nullable()->after('status_changed_at');
            }
        });

        // Add color and sort_order to task_statuses table
        Schema::table('task_statuses', function (Blueprint $table) {
            if (! Schema::hasColumn('task_statuses', 'color')) {
                $table->string('color', 20)->default('secondary')->after('description');
            }
            if (! Schema::hasColumn('task_statuses', 'sort_order')) {
                $table->unsignedInteger('sort_order')->default(0)->after('color');
            }
            if (! Schema::hasColumn('task_statuses', 'is_default')) {
                $table->boolean('is_default')->default(false)->after('sort_order');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['status_changed_at', 'time_in_status']);
        });
        Schema::table('task_statuses', function (Blueprint $table) {
            $table->dropColumn(['color', 'sort_order', 'is_default']);
        });
        Schema::dropIfExists('task_status_history');
    }
};
