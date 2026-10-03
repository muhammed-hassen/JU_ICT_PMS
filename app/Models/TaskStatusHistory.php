<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskStatusHistory extends Model
{
    protected $table = 'task_status_history';

    protected $fillable = [
        'task_id',
        'from_status_id',
        'to_status_id',
        'changed_by',
        'note',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function fromStatus(): BelongsTo
    {
        return $this->belongsTo(TaskStatus::class, 'from_status_id');
    }

    public function toStatus(): BelongsTo
    {
        return $this->belongsTo(TaskStatus::class, 'to_status_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
