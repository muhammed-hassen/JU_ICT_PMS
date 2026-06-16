<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TemplatePhase extends Model
{
    protected $fillable = [
        'project_template_id',
        'name',
        'description',
        'sort_order',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(ProjectTemplate::class, 'project_template_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(TemplateTask::class)->orderBy('sort_order');
    }
}
