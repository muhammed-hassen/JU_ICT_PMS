<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemplateTask extends Model
{
    protected $fillable = [
        'template_phase_id',
        'task_priority_id',
        'title',
        'description',
        'sort_order',
        'estimated_hours',
    ];

    public function phase(): BelongsTo
    {
        return $this->belongsTo(TemplatePhase::class, 'template_phase_id');
    }

    public function priority(): BelongsTo
    {
        return $this->belongsTo(TaskPriority::class, 'task_priority_id');
    }
}
