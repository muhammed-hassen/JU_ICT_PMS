<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    protected $fillable = [
        'name',
        'description',
        'team_leader_id',
        'parent_team_id', // For hierarchy (Director -> Team Leaders -> Teams)
    ];

    public function teamLeader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'team_leader_id');
    }

    public function parentTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'parent_team_id');
    }

    public function childTeams(): HasMany
    {
        return $this->hasMany(Team::class, 'parent_team_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_members');
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_teams');
    }
}
