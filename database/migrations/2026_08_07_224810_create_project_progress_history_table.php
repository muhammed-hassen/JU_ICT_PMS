<?php

// database/migrations/2026_08_08_000003_create_project_progress_history_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add progress_updated_at to projects table if missing
        Schema::table('projects', function (Blueprint $table) {
            if (! Schema::hasColumn('projects', 'progress_updated_at')) {
                $table->timestamp('progress_updated_at')->nullable()->after('progress_percentage');
            }
        });

        // Create progress history table
        if (! Schema::hasTable('project_progress_history')) {
            Schema::create('project_progress_history', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->constrained()->cascadeOnDelete();
                $table->decimal('progress_percentage', 5, 2)->default(0);
                $table->decimal('previous_progress', 5, 2)->default(0);
                $table->timestamp('recorded_at')->nullable();
                $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['project_id', 'recorded_at']);
            });
        }

        // Add completion date to projects if missing
        Schema::table('projects', function (Blueprint $table) {
            if (! Schema::hasColumn('projects', 'actual_completion_date')) {
                $table->timestamp('actual_completion_date')->nullable()->after('end_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['progress_updated_at', 'actual_completion_date']);
        });
        Schema::dropIfExists('project_progress_history');
    }
};
