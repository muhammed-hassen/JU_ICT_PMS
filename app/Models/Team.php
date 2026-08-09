<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes; // ← IMPORT

class Team extends Model
{
    use HasFactory;
    use SoftDeletes; // cool

    protected $fillable = [
        'name',
        'description',
        'team_leader_id',
        'parent_team_id',
    ];

    /**
     * Get the team leader
     */
    public function teamLeader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'team_leader_id');
    }

    /**
     * Get the parent team
     */
    public function parentTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'parent_team_id');
    }

    /**
     * Get the child teams
     */
    public function childTeams(): HasMany
    {
        return $this->hasMany(Team::class, 'parent_team_id');
    }

    /**
     * Get the members of the team
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_members');
    }

    /**
     * Get the projects assigned to the team
     */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_teams');
    }
}
