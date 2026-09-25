<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemplateTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_phase_id',
        'task_priority_id',
        'title',
        'description',
        'sort_order',
        'estimated_hours',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'estimated_hours' => 'decimal:2',
    ];

    /**
     * Get the template phase that owns this task
     */
    public function phase(): BelongsTo
    {
        return $this->belongsTo(TemplatePhase::class, 'template_phase_id');
    }

    /**
     * Get the priority of the template task
     */
    public function priority(): BelongsTo
    {
        return $this->belongsTo(TaskPriority::class, 'task_priority_id');
    }
}
