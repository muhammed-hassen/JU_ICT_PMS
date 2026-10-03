<?php

// app/Models/ActivityLog.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'loggable_type',
        'loggable_id',
        'user_id',
        'action',
        'event_type',
        'properties',
        'description',
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
    ];

    // ============================================================
    // RELATIONSHIPS
    // ============================================================

    public function loggable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ============================================================
    // HELPER METHODS
    // ============================================================

    public static function log($model, string $action, string $description, array $properties = []): self
    {
        return self::create([
            'loggable_type' => get_class($model),
            'loggable_id' => $model->id,
            'user_id' => auth()->id(),
            'action' => $action,
            'event_type' => self::getEventType($action),
            'properties' => $properties,
            'description' => $description,
        ]);
    }

    public static function getEventType(string $action): string
    {
        $eventMap = [
            'created' => 'creation',
            'updated' => 'update',
            'deleted' => 'deletion',
            'restored' => 'restoration',
            'status_changed' => 'status_change',
            'assigned' => 'assignment',
            'reassigned' => 'reassignment',
            'progress_updated' => 'progress_update',
            'reordered' => 'reorder',
            'completed' => 'completion',
            'reopened' => 'reopened',
        ];

        return $eventMap[$action] ?? 'general';
    }

    public function getIconAttribute(): string
    {
        $icons = [
            'created' => 'fa-plus-circle text-success',
            'updated' => 'fa-edit text-warning',
            'deleted' => 'fa-trash text-danger',
            'restored' => 'fa-undo text-info',
            'status_changed' => 'fa-exchange-alt text-primary',
            'assigned' => 'fa-user-plus text-info',
            'reassigned' => 'fa-user-edit text-warning',
            'progress_updated' => 'fa-chart-line text-success',
            'reordered' => 'fa-arrows-alt text-secondary',
            'completed' => 'fa-check-circle text-success',
            'reopened' => 'fa-folder-open text-warning',
        ];

        return $icons[$this->action] ?? 'fa-circle text-secondary';
    }

    public function getActionLabelAttribute(): string
    {
        $labels = [
            'created' => 'Created',
            'updated' => 'Updated',
            'deleted' => 'Deleted',
            'restored' => 'Restored',
            'status_changed' => 'Changed Status',
            'assigned' => 'Assigned',
            'reassigned' => 'Reassigned',
            'progress_updated' => 'Updated Progress',
            'reordered' => 'Reordered',
            'completed' => 'Completed',
            'reopened' => 'Reopened',
        ];

        return $labels[$this->action] ?? ucfirst($this->action);
    }

    public function getTypeLabelAttribute(): string
    {
        $types = [
            'App\Models\Project' => 'Project',
            'App\Models\Phase' => 'Phase',
            'App\Models\Task' => 'Task',
            'App\Models\Team' => 'Team',
            'App\Models\User' => 'User',
        ];

        return $types[$this->loggable_type] ?? 'Item';
    }

    public function getColorAttribute(): string
    {
        $colors = [
            'created' => 'success',
            'updated' => 'warning',
            'deleted' => 'danger',
            'restored' => 'info',
            'status_changed' => 'primary',
            'assigned' => 'info',
            'reassigned' => 'warning',
            'progress_updated' => 'success',
            'reordered' => 'secondary',
            'completed' => 'success',
            'reopened' => 'warning',
        ];

        return $colors[$this->action] ?? 'secondary';
    }
}
