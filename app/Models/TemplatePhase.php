<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TemplatePhase extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_template_id',
        'name',
        'description',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    /**
     * Get the template that owns this phase
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(ProjectTemplate::class, 'project_template_id');
    }

    /**
     * Get the tasks for the template phase
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(TemplateTask::class)->orderBy('sort_order');
    }
}
