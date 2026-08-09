<?php

// app/Models/TaskStatus.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskStatus extends Model
{
    protected $fillable = [
        'name',
        'description',
        'color',
        'sort_order',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'task_status_id');
    }

    public function historyFrom(): HasMany
    {
        return $this->hasMany(TaskStatusHistory::class, 'from_status_id');
    }

    public function historyTo(): HasMany
    {
        return $this->hasMany(TaskStatusHistory::class, 'to_status_id');
    }

    public function getColorClassAttribute(): string
    {
        return $this->color ?? 'secondary';
    }
}
