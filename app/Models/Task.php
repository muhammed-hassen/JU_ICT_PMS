<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Task extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'phase_id',
        'task_status_id',
        'task_priority_id',
        'assigned_to',
        'title',
        'sort_order',
        'description',
        'estimated_hours',
        'actual_hours',
        'start_date',
        'deadline',
        'progress_percentage',
        'completed_at',
        'reviewed_by',
        'reviewed_at',
        'status_updated_at',
        'status_changed_by',
        'status_changed_at',
        'time_in_status',
        'priority_score',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'deadline' => 'date',
        'completed_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'status_updated_at' => 'datetime',
        'status_changed_at' => 'datetime',
        'estimated_hours' => 'decimal:2',
        'actual_hours' => 'decimal:2',
        'progress_percentage' => 'decimal:2',
        'time_in_status' => 'array',
        'priority_score' => 'integer',
    ];

    // ============================================================
    // RELATIONSHIPS
    // ============================================================

    public function phase(): BelongsTo
    {
        return $this->belongsTo(Phase::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(TaskStatus::class, 'task_status_id');
    }

    public function priority(): BelongsTo
    {
        return $this->belongsTo(TaskPriority::class, 'task_priority_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function statusChanger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'status_changed_by');
    }

    public function assignedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_assignments', 'task_id', 'user_id')
            ->withPivot('assigned_by', 'assigned_at', 'note')
            ->withTimestamps();
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(TaskStatusHistory::class)->orderBy('created_at', 'desc');
    }

    // ============================================================
    // SCOPES
    // ============================================================

    public function scopeOverdue($query)
    {
        return $query->where('deadline', '<', now())
            ->where('progress_percentage', '<', 100)
            ->whereNull('completed_at');
    }

    public function scopeDueToday($query)
    {
        return $query->whereDate('deadline', now()->toDateString())
            ->where('progress_percentage', '<', 100)
            ->whereNull('completed_at');
    }

    public function scopeByStatus($query, $statusId)
    {
        if ($statusId) {
            return $query->where('task_status_id', $statusId);
        }

        return $query;
    }

    public function scopeByPriority($query, $priorityId)
    {
        if ($priorityId) {
            return $query->where('task_priority_id', $priorityId);
        }

        return $query;
    }

    public function scopeAssignedTo($query, $userId)
    {
        if ($userId) {
            return $query->where('assigned_to', $userId);
        }

        return $query;
    }

    public function scopeAssignedToUser($query, $userId)
    {
        if ($userId) {
            return $query->where('assigned_to', $userId)
                ->orWhereHas('assignedUsers', function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                });
        }

        return $query;
    }

    public function scopeForUser($query, $userId)
    {
        if ($userId) {
            return $query->where('assigned_to', $userId)
                ->orWhere('created_by', $userId)
                ->orWhereHas('assignedUsers', function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                });
        }

        return $query;
    }

    public function scopeInPhase($query, $phaseId)
    {
        if ($phaseId) {
            return $query->where('phase_id', $phaseId);
        }

        return $query;
    }

    public function scopeInProject($query, $projectId)
    {
        if ($projectId) {
            return $query->whereHas('phase', function ($q) use ($projectId) {
                $q->where('project_id', $projectId);
            });
        }

        return $query;
    }

    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    public function scopeOrderByPriority($query)
    {
        return $query->orderByRaw("
            CASE 
                WHEN task_priority_id = (SELECT id FROM task_priorities WHERE name = 'Critical') THEN 1
                WHEN task_priority_id = (SELECT id FROM task_priorities WHERE name = 'High') THEN 2
                WHEN task_priority_id = (SELECT id FROM task_priorities WHERE name = 'Medium') THEN 3
                WHEN task_priority_id = (SELECT id FROM task_priorities WHERE name = 'Low') THEN 4
                ELSE 5
            END
        ");
    }

    public function scopeByPriorityLevel($query, $level)
    {
        if ($level) {
            return $query->whereHas('priority', function ($q) use ($level) {
                $q->where('name', $level);
            });
        }

        return $query;
    }

    // ============================================================
    // STATUS WORKFLOW METHODS
    // ============================================================

    protected array $validTransitions = [
        'Not Started' => ['In Progress'],
        'In Progress' => ['Under Review', 'Blocked'],
        'Under Review' => ['Completed', 'In Progress', 'Blocked'],
        'Blocked' => ['In Progress'],
        'Completed' => ['Under Review'],
    ];

    public function canTransitionTo(string $newStatusName): bool
    {
        $currentStatus = $this->status?->name ?? 'Not Started';

        if ($currentStatus === $newStatusName) {
            return true;
        }

        return in_array($newStatusName, $this->validTransitions[$currentStatus] ?? []);
    }

    public function getAvailableTransitions(): array
    {
        $currentStatus = $this->status?->name ?? 'Not Started';

        return $this->validTransitions[$currentStatus] ?? [];
    }

    public function transitionTo(TaskStatus $newStatus, ?string $note = null): bool
    {
        $oldStatus = $this->status;

        if (! $this->canTransitionTo($newStatus->name)) {
            return false;
        }

        DB::transaction(function () use ($newStatus, $oldStatus, $note) {
            $this->update([
                'task_status_id' => $newStatus->id,
                'status_updated_at' => now(),
                'status_changed_at' => now(),
                'status_changed_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            if ($newStatus->name === 'Completed') {
                $this->update([
                    'completed_at' => now(),
                    'progress_percentage' => 100,
                ]);
            }

            if ($oldStatus && $oldStatus->name === 'Completed' && $newStatus->name !== 'Completed') {
                $this->update([
                    'completed_at' => null,
                ]);
            }

            TaskStatusHistory::create([
                'task_id' => $this->id,
                'from_status_id' => $oldStatus?->id,
                'to_status_id' => $newStatus->id,
                'changed_by' => auth()->id(),
                'note' => $note,
            ]);

            $this->updateTimeInStatus($oldStatus);
        });

        if ($this->phase) {
            $this->phase->updateProgress();
            if ($this->phase->project) {
                $this->phase->project->update(['progress_percentage' => $this->phase->project->getProgressAttribute()]);
            }
        }

        return true;
    }

    protected function updateTimeInStatus(?TaskStatus $oldStatus): void
    {
        if (! $oldStatus) {
            return;
        }

        $timeInStatus = $this->time_in_status ?? [];
        $key = $oldStatus->name;

        if (! isset($timeInStatus[$key])) {
            $timeInStatus[$key] = 0;
        }

        $timeInStatus[$key] += $this->status_changed_at
            ? now()->diffInSeconds($this->status_changed_at)
            : 0;

        $this->update(['time_in_status' => $timeInStatus]);
    }

    public function getTimeInStatus(string $statusName): int
    {
        $timeInStatus = $this->time_in_status ?? [];

        return $timeInStatus[$statusName] ?? 0;
    }

    public function getFormattedTimeInStatus(string $statusName): string
    {
        $seconds = $this->getTimeInStatus($statusName);
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        if ($days > 0) {
            return "{$days}d {$hours}h";
        }
        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }

        return "{$minutes}m";
    }

    // ============================================================
    // ASSIGNMENT METHODS
    // ============================================================

    public function isAssignedTo(User $user): bool
    {
        if ($this->assigned_to === $user->id) {
            return true;
        }

        return $this->assignedUsers()->where('user_id', $user->id)->exists();
    }

    public function assignTo(User $user, ?string $note = null): void
    {
        $this->assignedUsers()->syncWithoutDetaching([$user->id => [
            'assigned_by' => auth()->id(),
            'assigned_at' => now(),
            'note' => $note,
        ]]);

        if (! $this->assigned_to) {
            $this->update(['assigned_to' => $user->id]);
        }
    }

    public function unassignFrom(User $user): void
    {
        $this->assignedUsers()->detach($user->id);

        if ($this->assigned_to === $user->id) {
            $this->update(['assigned_to' => null]);
        }
    }

    public function setPrimaryAssignee(User $user): void
    {
        if (! $this->isAssignedTo($user)) {
            $this->assignTo($user);
        }
        $this->update(['assigned_to' => $user->id]);
    }

    // ============================================================
    // HELPER METHODS
    // ============================================================

    public function isOverdue(): bool
    {
        return $this->deadline &&
               $this->deadline->isPast() &&
               $this->progress_percentage < 100 &&
               ! $this->completed_at;
    }

    public function getStatusColorAttribute(): string
    {
        $colors = [
            'Not Started' => 'secondary',
            'In Progress' => 'primary',
            'Under Review' => 'warning',
            'Completed' => 'success',
            'Blocked' => 'danger',
            'Cancelled' => 'dark',
        ];

        return $colors[$this->status?->name] ?? 'secondary';
    }

    public function getPriorityColorAttribute(): string
    {
        $colors = [
            'Critical' => 'danger',
            'High' => 'warning',
            'Medium' => 'info',
            'Low' => 'success',
        ];

        return $colors[$this->priority?->name] ?? 'secondary';
    }

    public function getRemainingTimeAttribute(): ?string
    {
        if (! $this->deadline || $this->completed_at) {
            return null;
        }

        $days = now()->diffInDays($this->deadline, false);

        if ($days > 0) {
            return "{$days} days remaining";
        } elseif ($days == 0) {
            return 'Due today!';
        } else {
            return abs($days).' days overdue';
        }
    }

    public function getDaysOverdueAttribute(): ?int
    {
        if (! $this->deadline || $this->completed_at) {
            return null;
        }
        if ($this->deadline->isPast()) {
            return abs($this->deadline->diffInDays(now()));
        }

        return 0;
    }

    public function getDaysRemainingAttribute(): ?int
    {
        if (! $this->deadline || $this->completed_at) {
            return null;
        }
        if ($this->deadline->isFuture()) {
            return now()->diffInDays($this->deadline);
        }

        return 0;
    }

    // ============================================================
    // PRIORITY & DEADLINE METHODS
    // ============================================================

    public function calculatePriorityScore(): int
    {
        $score = 0;

        $priorityWeights = [
            'Low' => 10,
            'Medium' => 20,
            'High' => 30,
            'Critical' => 40,
        ];
        $score += $priorityWeights[$this->priority?->name] ?? 0;

        if ($this->deadline) {
            $daysUntilDeadline = now()->diffInDays($this->deadline, false);
            if ($daysUntilDeadline < 0) {
                $score += 20;
            } elseif ($daysUntilDeadline < 3) {
                $score += 10;
            } elseif ($daysUntilDeadline < 7) {
                $score += 5;
            }
        }

        return $score;
    }

    public function getPriorityLevelAttribute(): string
    {
        $score = $this->calculatePriorityScore();

        if ($score >= 60) {
            return 'Critical';
        }
        if ($score >= 45) {
            return 'High';
        }
        if ($score >= 25) {
            return 'Medium';
        }

        return 'Low';
    }

    public function getPriorityBadgeColorAttribute(): string
    {
        $level = $this->getPriorityLevelAttribute();

        $colors = [
            'Critical' => 'danger',
            'High' => 'warning',
            'Medium' => 'info',
            'Low' => 'success',
        ];

        return $colors[$level] ?? 'secondary';
    }

    public function getDeadlineStatusAttribute(): string
    {
        if (! $this->deadline) {
            return 'none';
        }

        if ($this->isOverdue()) {
            return 'overdue';
        }

        $daysLeft = now()->diffInDays($this->deadline, false);
        if ($daysLeft <= 3) {
            return 'urgent';
        }

        if ($daysLeft <= 7) {
            return 'soon';
        }

        return 'on_track';
    }

    public function getDeadlineColorAttribute(): string
    {
        $status = $this->getDeadlineStatusAttribute();

        $colors = [
            'overdue' => 'danger',
            'urgent' => 'warning',
            'soon' => 'info',
            'on_track' => 'success',
            'none' => 'secondary',
        ];

        return $colors[$status] ?? 'secondary';
    }

    public function getDeadlineBadgeAttribute(): string
    {
        if (! $this->deadline) {
            return 'No deadline';
        }

        if ($this->isOverdue()) {
            return 'Overdue';
        }

        $daysLeft = now()->diffInDays($this->deadline, false);
        if ($daysLeft <= 3) {
            return "Due in {$daysLeft}d";
        }

        if ($daysLeft <= 7) {
            return "Due in {$daysLeft}d";
        }

        return $this->deadline->format('M d, Y');
    }

    public function getPriorityScoreAttribute(): int
    {
        return $this->calculatePriorityScore();
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->isOverdue();
    }
}
