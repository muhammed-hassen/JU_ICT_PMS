<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A checkpoint inside a phase: what must be delivered, and by when. */
class PhaseMilestone extends Model
{
    protected $fillable = ['phase_id', 'title', 'deliverable', 'due_date', 'completed_at', 'created_by'];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function phase(): BelongsTo
    {
        return $this->belongsTo(Phase::class);
    }

    public function isOverdue(): bool
    {
        return ! $this->completed_at && $this->due_date && $this->due_date->isPast() && ! $this->due_date->isToday();
    }
}
