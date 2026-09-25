<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Phase extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'phase_status_id',
        'name',
        'description',
        'sort_order',
        'progress_percentage',
        'start_date',
        'end_date',
        'actual_start_date',
        'actual_end_date',
        'estimated_days',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'progress_percentage' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'actual_start_date' => 'date',
        'actual_end_date' => 'date',
        'estimated_days' => 'integer',
    ];

    // ========== RELATIONSHIPS ==========

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(PhaseStatus::class, 'phase_status_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class)->orderBy('sort_order');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ========== HELPER METHODS ==========

    /**
     * Calculate progress based on tasks
     */
    public function calculateProgress(): float
    {
        $tasks = $this->tasks;

        if ($tasks->isEmpty()) {
            return 0;
        }

        $totalProgress = $tasks->sum('progress_percentage');

        return round($totalProgress / $tasks->count(), 2);
    }

    /**
     * Update phase progress percentage
     */
    public function updateProgress(): void
    {
        $this->update([
            'progress_percentage' => $this->calculateProgress(),
        ]);
    }

    /**
     * Get task statistics for this phase
     * FIXED: Using Collection methods instead of whereHas()
     */
    public function getTaskStatsAttribute(): array
    {
        $tasks = $this->tasks;
        $total = $tasks->count();

        if ($total === 0) {
            return [
                'total' => 0,
                'completed' => 0,
                'in_progress' => 0,
                'pending' => 0,
                'blocked' => 0,
                'not_started' => 0,
                'percentage' => 0,
            ];
        }

        // Use Collection methods
        $completed = $tasks->where('progress_percentage', 100)->count();
        $inProgress = $tasks->whereBetween('progress_percentage', [1, 99])->count();
        $notStarted = $tasks->where('progress_percentage', 0)->count();

        // Filter for blocked tasks using Collection
        $blocked = $tasks->filter(function ($task) {
            return $task->status && $task->status->name === 'Blocked';
        })->count();

        return [
            'total' => $total,
            'completed' => $completed,
            'in_progress' => $inProgress,
            'pending' => $total - $completed - $inProgress,
            'blocked' => $blocked,
            'not_started' => $notStarted,
            'percentage' => round($this->progress_percentage, 1),
        ];
    }

    /**
     * Get status color for display
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status?->name) {
            'Completed' => 'success',
            'In Progress' => 'info',
            'Blocked' => 'danger',
            'Not Started' => 'secondary',
            default => 'secondary'
        };
    }

    /**
     * Check if phase is complete
     */
    public function isComplete(): bool
    {
        return $this->progress_percentage >= 100;
    }

    /**
     * Get next phase in project
     */
    public function getNextPhaseAttribute(): ?Phase
    {
        return $this->project->phases()
            ->where('sort_order', '>', $this->sort_order)
            ->orderBy('sort_order')
            ->first();
    }

    /**
     * Get previous phase in project
     */
    public function getPreviousPhaseAttribute(): ?Phase
    {
        return $this->project->phases()
            ->where('sort_order', '<', $this->sort_order)
            ->orderBy('sort_order', 'desc')
            ->first();
    }

    /**
     * Get planned duration in days
     */
    public function getPlannedDurationAttribute(): ?int
    {
        if ($this->start_date && $this->end_date) {
            return $this->start_date->diffInDays($this->end_date);
        }

        return null;
    }

    /**
     * Get actual duration in days
     */
    public function getActualDurationAttribute(): ?int
    {
        if ($this->actual_start_date && $this->actual_end_date) {
            return $this->actual_start_date->diffInDays($this->actual_end_date);
        }

        return null;
    }

    /**
     * Check if phase is overdue
     */
    public function isOverdue(): bool
    {
        if (! $this->end_date || $this->isComplete()) {
            return false;
        }

        return $this->end_date->isPast();
    }

    /**
     * Get days remaining until end date
     */
    public function getDaysRemainingAttribute(): ?int
    {
        if (! $this->end_date || $this->isComplete()) {
            return null;
        }

        if ($this->end_date->isPast()) {
            return 0;
        }

        return now()->diffInDays($this->end_date, false);
    }

    /**
     * Get days overdue
     */
    public function getDaysOverdueAttribute(): ?int
    {
        if (! $this->end_date || $this->isComplete()) {
            return null;
        }

        if ($this->end_date->isPast()) {
            return abs($this->end_date->diffInDays(now()));
        }

        return 0;
    }

    /**
     * Check if phase is on track based on dates and progress
     */
    public function isOnTrack(): bool
    {
        if (! $this->start_date || ! $this->end_date || $this->isComplete()) {
            return true;
        }

        $totalDays = $this->start_date->diffInDays($this->end_date);
        $elapsedDays = $this->start_date->diffInDays(now());

        if ($totalDays <= 0) {
            return true;
        }

        $expectedProgress = min(($elapsedDays / $totalDays) * 100, 100);
        $actualProgress = $this->progress_percentage;

        // Allow 20% variance
        return $actualProgress >= ($expectedProgress - 20);
    }

    /**
     * Update actual start date when phase starts
     */
    public function startPhase(): void
    {
        if (! $this->actual_start_date) {
            $this->update([
                'actual_start_date' => now(),
                'phase_status_id' => $this->getStatusIdByName('In Progress'),
                'updated_by' => auth()->id(),
            ]);
        }
    }

    /**
     * Update actual end date when phase completes
     */
    public function completePhase(): void
    {
        if (! $this->actual_end_date && $this->progress_percentage >= 100) {
            $this->update([
                'actual_end_date' => now(),
                'phase_status_id' => $this->getStatusIdByName('Completed'),
                'updated_by' => auth()->id(),
            ]);
        }
    }

    /**
     * Helper to get status ID by name
     */
    protected function getStatusIdByName(string $name): ?int
    {
        return PhaseStatus::where('name', $name)->value('id');
    }

    // ========== SCOPES ==========

    /**
     * Scope for overdue phases
     */
    public function scopeOverdue($query)
    {
        return $query->whereNotNull('end_date')
            ->where('end_date', '<', now())
            ->where('progress_percentage', '<', 100);
    }

    /**
     * Scope for phases ending soon (within 7 days)
     */
    public function scopeEndingSoon($query, int $days = 7)
    {
        return $query->whereNotNull('end_date')
            ->whereBetween('end_date', [now(), now()->addDays($days)])
            ->where('progress_percentage', '<', 100);
    }
}
