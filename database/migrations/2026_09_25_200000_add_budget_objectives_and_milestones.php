<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SRS 5.4 to 5.6: projects carry objectives and a budget, tasks carry cost and
 * resources, and each phase has milestones with a deadline and a deliverable.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->text('objectives')->nullable()->after('description');
            $table->decimal('budget', 14, 2)->nullable()->after('objectives');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->decimal('estimated_cost', 12, 2)->nullable()->after('actual_hours');
            $table->decimal('actual_cost', 12, 2)->nullable()->after('estimated_cost');
            $table->text('resources')->nullable()->after('actual_cost');
        });

        // Phase already reads and writes these (startPhase, completePhase), but no migration created them.
        Schema::table('phases', function (Blueprint $table) {
            if (! Schema::hasColumn('phases', 'actual_start_date')) {
                $table->date('actual_start_date')->nullable()->after('end_date');
            }
            if (! Schema::hasColumn('phases', 'actual_end_date')) {
                $table->date('actual_end_date')->nullable()->after('actual_start_date');
            }
        });

        Schema::create('phase_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('phase_id')->constrained('phases')->cascadeOnDelete();
            $table->string('title', 200);
            $table->string('deliverable', 255)->nullable();
            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phase_milestones');

        Schema::table('phases', function (Blueprint $table) {
            $table->dropColumn(['actual_start_date', 'actual_end_date']);
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['estimated_cost', 'actual_cost', 'resources']);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['objectives', 'budget']);
        });
    }
};
