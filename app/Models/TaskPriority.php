<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskPriority extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'level_order',
    ];

    /**
     * Get the tasks with this priority
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'task_priority_id');
    }
}
