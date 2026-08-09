<?php

// app/Models/ProjectProgressHistory.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectProgressHistory extends Model
{
    protected $table = 'project_progress_history';

    protected $fillable = [
        'project_id',
        'progress_percentage',
        'previous_progress',
        'recorded_at',
        'recorded_by',
    ];

    protected $casts = [
        'progress_percentage' => 'decimal:2',
        'previous_progress' => 'decimal:2',
        'recorded_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
