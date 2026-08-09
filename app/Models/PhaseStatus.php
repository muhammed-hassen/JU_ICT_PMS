<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhaseStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Get the phases with this status
     */
    public function phases(): HasMany
    {
        return $this->hasMany(Phase::class, 'phase_status_id');
    }
}
