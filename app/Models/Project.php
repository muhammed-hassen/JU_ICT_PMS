<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'template_id',
        'name',
        'description',
        'status',
        'start_date',
        'end_date',
        'actual_completion_date',
        'progress_percentage',
        'progress_updated_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'actual_completion_date' => 'datetime',
        'progress_percentage' => 'decimal:2',
        'progress_updated_at' => 'datetime',
    ];

    // ============================================================
    // RELATIONSHIPS
    // ============================================================

    public function template(): BelongsTo
    {
        return $this->belongsTo(ProjectTemplate::class, 'template_id');
    }

    public function phases(): HasMany
    {
        return $this->hasMany(Phase::class)->orderBy('sort_order');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'project_teams');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members');
    }

    public function progressHistory(): HasMany
    {
        return $this->hasMany(ProjectProgressHistory::class)->orderBy('recorded_at', 'desc');
    }

    // ============================================================
    // PROGRESS METHODS
    // ============================================================

    /**
     * Calculate project progress based on phases
     */
    public function getProgressAttribute(): float
    {
        $phases = $this->phases;
        if ($phases->isEmpty()) {
            return 0;
        }

        $totalProgress = $phases->sum('progress_percentage');

        return round($totalProgress / $phases->count(), 2);
    }

    /**
     * Update progress with history tracking
     */
    public function updateProgressWithHistory(): void
    {
        $newProgress = $this->getProgressAttribute();
        $oldProgress = $this->progress_percentage ?? 0;

        if ($newProgress != $oldProgress) {
            ProjectProgressHistory::create([
                'project_id' => $this->id,
                'progress_percentage' => $newProgress,
                'previous_progress' => $oldProgress,
                'recorded_at' => now(),
                'recorded_by' => auth()->id(),
            ]);

            $this->update([
                'progress_percentage' => $newProgress,
                'progress_updated_at' => now(),
            ]);

            if ($newProgress >= 100 && ! $this->actual_completion_date) {
                $this->update(['actual_completion_date' => now()]);
            }
        }
    }

    /**
     * Get progress trend (last 7 days)
     */
    public function getProgressTrendAttribute(): array
    {
        $history = $this->progressHistory()->take(7)->get()->reverse();

        $labels = [];
        $data = [];

        foreach ($history as $record) {
            $labels[] = $record->recorded_at->format('M d');
            $data[] = $record->progress_percentage;
        }

        if (empty($data)) {
            $labels[] = now()->format('M d');
            $data[] = $this->progress_percentage ?? 0;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Get phase progress breakdown
     */
    public function getPhaseProgressBreakdownAttribute(): array
    {
        $phases = $this->phases;

        $labels = [];
        $data = [];
        $colors = ['#17a2b8', '#28a745', '#ffc107', '#dc3545', '#6610f2', '#fd7e14', '#20c997', '#e83e8c'];

        foreach ($phases as $index => $phase) {
            $labels[] = $phase->name;
            $data[] = $phase->progress_percentage;
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'colors' => array_slice($colors, 0, count($labels)),
        ];
    }

    /**
     * Get project timeline data for Gantt-like view
     */
    public function getTimelineDataAttribute(): array
    {
        $phases = $this->phases;
        $timeline = [];

        foreach ($phases as $phase) {
            $timeline[] = [
                'id' => $phase->id,
                'name' => $phase->name,
                'start' => $phase->start_date ? $phase->start_date->format('Y-m-d') : null,
                'end' => $phase->end_date ? $phase->end_date->format('Y-m-d') : null,
                'progress' => $phase->progress_percentage,
                'status' => $phase->status?->name ?? 'Not Started',
                'color' => $phase->status_color,
            ];
        }

        return $timeline;
    }

    /**
     * Get project stats for dashboard
     */
    public function getProgressStatsAttribute(): array
    {
        $phases = $this->phases;
        $totalPhases = $phases->count();
        $completedPhases = $phases->where('progress_percentage', 100)->count();
        $activePhases = $phases->whereBetween('progress_percentage', [1, 99])->count();
        $notStartedPhases = $phases->where('progress_percentage', 0)->count();

        $tasks = Task::whereIn('phase_id', $phases->pluck('id'))->get();
        $totalTasks = $tasks->count();
        $completedTasks = $tasks->where('progress_percentage', 100)->count();
        $inProgressTasks = $tasks->whereBetween('progress_percentage', [1, 99])->count();
        $notStartedTasks = $tasks->where('progress_percentage', 0)->count();

        return [
            'total_phases' => $totalPhases,
            'completed_phases' => $completedPhases,
            'active_phases' => $activePhases,
            'not_started_phases' => $notStartedPhases,
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'in_progress_tasks' => $inProgressTasks,
            'not_started_tasks' => $notStartedTasks,
            'overall_progress' => $this->progress_percentage ?? 0,
        ];
    }
}
