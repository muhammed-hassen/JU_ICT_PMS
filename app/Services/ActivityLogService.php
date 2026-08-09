<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class ActivityLogService
{
    /**
     * Log an activity
     */
    public function log(Model $model, string $action, string $description, array $properties = []): ActivityLog
    {
        return ActivityLog::create([
            'loggable_type' => get_class($model),
            'loggable_id' => $model->id,
            'user_id' => auth()->id() ?? 1, // Fallback to system user
            'action' => $action,
            'event_type' => $this->getEventType($action),
            'properties' => $properties,
            'description' => $description,
        ]);
    }

    /**
     * Get event type from action
     */
    protected function getEventType(string $action): string
    {
        $eventMap = [
            'created' => 'creation',
            'updated' => 'update',
            'deleted' => 'deletion',
            'status_changed' => 'status_change',
            'assigned' => 'assignment',
            'reassigned' => 'reassignment',
            'progress_updated' => 'progress_update',
            'reordered' => 'reorder',
        ];

        return $eventMap[$action] ?? 'general';
    }
}
