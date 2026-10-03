<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Subtasks: the last level of the hierarchy in the development plan
 * (Project -> Phase -> Task -> SubTask). A task's checklist.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subtasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->string('title', 200);
            $table->boolean('is_done')->default(false);
            $table->unsignedInteger('sort_order')->default(1);
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['task_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subtasks');
    }
};
