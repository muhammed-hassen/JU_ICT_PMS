<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    protected $fillable = [
        'phase_id',
        'task_status_id',
        'task_priority_id',
        'assigned_to',
        'title',
        'description',
        'estimated_hours',
        'progress_percentage',
        'created_by',
        'updated_by',
    ];

    public function phase(): BelongsTo
    {
        return $this->belongsTo(Phase::class);
    }
}
