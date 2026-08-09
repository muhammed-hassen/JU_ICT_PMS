<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_priorities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->unsignedInteger('level_order')->unique();
            $table->timestamps();
        });

        Schema::create('phase_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('task_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('project_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('template_phases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_template_id')->constrained('project_templates')->cascadeOnDelete();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order');
            $table->timestamps();

            $table->unique(['project_template_id', 'sort_order']);
        });

        Schema::create('template_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_phase_id')->constrained('template_phases')->cascadeOnDelete();
            $table->foreignId('task_priority_id')->nullable()->constrained('task_priorities')->nullOnDelete();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order');
            $table->decimal('estimated_hours', 8, 2)->nullable();
            $table->timestamps();

            $table->unique(['template_phase_id', 'sort_order']);
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->nullable()->constrained('project_templates')->nullOnDelete();
            $table->string('name', 200);
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('phases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('phase_status_id')->constrained('phase_statuses');
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order');
            $table->decimal('progress_percentage', 5, 2)->default(0);
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['project_id', 'sort_order']);
        });

        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('phase_id')->constrained('phases')->cascadeOnDelete();
            $table->foreignId('task_status_id')->constrained('task_statuses');
            $table->foreignId('task_priority_id')->nullable()->constrained('task_priorities')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->decimal('estimated_hours', 8, 2)->nullable();
            $table->decimal('progress_percentage', 5, 2)->default(0);
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('phases');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('template_tasks');
        Schema::dropIfExists('template_phases');
        Schema::dropIfExists('project_templates');
        Schema::dropIfExists('task_statuses');
        Schema::dropIfExists('phase_statuses');
        Schema::dropIfExists('task_priorities');
    }
};
