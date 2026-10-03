<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ========== ADD COLUMNS TO TASKS TABLE ==========
        Schema::table('tasks', function (Blueprint $table) {
            if (! Schema::hasColumn('tasks', 'deadline')) {
                $table->date('deadline')->nullable()->after('estimated_hours');
            }
            if (! Schema::hasColumn('tasks', 'start_date')) {
                $table->date('start_date')->nullable()->after('estimated_hours');
            }
            if (! Schema::hasColumn('tasks', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('progress_percentage');
            }
            if (! Schema::hasColumn('tasks', 'reviewed_by')) {
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('tasks', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable();
            }
        });

        // ========== CREATE ACTIVITY_LOGS TABLE ==========
        if (! Schema::hasTable('activity_logs')) {
            Schema::create('activity_logs', function (Blueprint $table) {
                $table->id();
                // morphs() automatically creates the indexes - NO need to add them again!
                $table->morphs('loggable');
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('action', 100);
                $table->string('event_type', 50);
                $table->json('properties')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();

                // ONLY add the created_at index
                $table->index('created_at');
            });
        }

        // ========== ADD INDEXES TO TASKS ==========
        Schema::table('tasks', function (Blueprint $table) {
            if (! Schema::hasIndex('tasks', 'tasks_deadline_index')) {
                $table->index('deadline', 'tasks_deadline_index');
            }
            if (! Schema::hasIndex('tasks', 'tasks_assigned_to_index')) {
                $table->index('assigned_to', 'tasks_assigned_to_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $columns = ['deadline', 'start_date', 'completed_at', 'reviewed_by', 'reviewed_at'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('tasks', $column)) {
                    if ($column === 'reviewed_by') {
                        try {
                            $table->dropForeign(['reviewed_by']);
                        } catch (Exception $e) {
                        }
                    }
                    $table->dropColumn($column);
                }
            }
        });

        Schema::dropIfExists('activity_logs');
    }
};
