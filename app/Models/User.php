<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;

    protected string $guard_name = 'web';

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ========== RELATIONSHIPS ==========

    public function ledTeams(): HasMany
    {
        return $this->hasMany(Team::class, 'team_leader_id');
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_members');
    }

    public function assignedTasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_assignments', 'user_id', 'task_id')
            ->withPivot('assigned_by', 'assigned_at', 'note')
            ->withTimestamps();
    }

    /**
     * Tasks where this user is the assignee (tasks.assigned_to).
     * Used by the reports page; it was called but never defined.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    public function createdTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    // ========== ROLE CHECK METHODS ==========

    /** Sees all project work. The System Administrator is not included: that role runs accounts, not projects. */
    public function isDirector(): bool
    {
        return $this->hasRole(['ICT Director', 'Super Admin']);
    }

    public function isTeamLeader(): bool
    {
        return $this->hasRole('Team Leader') || $this->isDirector();
    }

    public function isSystemAdmin(): bool
    {
        return $this->hasRole('System Administrator');
    }

    // ========== TEAM METHODS ==========

    public function getTeamIds(): array
    {
        if ($this->isDirector()) {
            return Team::all()->pluck('id')->toArray();
        }

        $teamIds = $this->teams()->pluck('teams.id')->toArray();

        if ($this->isTeamLeader()) {
            $ledTeamIds = $this->ledTeams()->pluck('id')->toArray();
            $teamIds = array_merge($teamIds, $ledTeamIds);
        }

        return array_unique($teamIds);
    }

    /**
     * People this user may start a conversation with: anyone in one of their
     * teams (its members and its leader) plus the Director and System
     * Administrators. Team Leaders can also reach the other Team Leaders.
     * The Director and System Administrators can message everyone.
     */
    public function messageableUsers(): Collection
    {
        $query = User::where('id', '!=', $this->id)->orderBy('name');

        if (! $this->isDirector() && ! $this->isSystemAdmin()) {
            $teamIds = $this->getTeamIds();
            $query->where(function ($q) use ($teamIds) {
                $q->whereHas('teams', fn ($t) => $t->whereIn('teams.id', $teamIds))
                    ->orWhereHas('ledTeams', fn ($t) => $t->whereIn('id', $teamIds))
                    ->orWhereHas('roles', fn ($r) => $r->whereIn('name', $this->isTeamLeader()
                        // Leaders coordinate across teams, so they reach every other leader.
                        ? ['ICT Director', 'System Administrator', 'Super Admin', 'Team Leader']
                        : ['ICT Director', 'System Administrator', 'Super Admin']));
            });
        }

        return $query->get();
    }

    public function conversations(): BelongsToMany
    {
        return $this->belongsToMany(Conversation::class, 'conversation_participants')
            ->withPivot('last_read_at')
            ->withTimestamps();
    }

    /** Conversations with at least one message this user has not seen. */
    public function unreadConversationCount(): int
    {
        return $this->conversations()
            ->whereHas('messages', function ($q) {
                $q->where('messages.user_id', '!=', $this->id)
                    ->where(function ($w) {
                        $w->whereNull('conversation_participants.last_read_at')
                            ->orWhereColumn('messages.created_at', '>', 'conversation_participants.last_read_at');
                    });
            })
            ->count();
    }

    public function getTeamMembers(): Collection
    {
        $teamIds = $this->getTeamIds();

        return User::whereHas('teams', function ($query) use ($teamIds) {
            $query->whereIn('teams.id', $teamIds);
        })->orWhereHas('ledTeams', function ($query) use ($teamIds) {
            $query->whereIn('id', $teamIds);
        })->get();
    }

    // ========== PROJECT METHODS ==========

    public function getVisibleProjects(): Collection
    {
        if ($this->isDirector()) {
            return Project::all();
        }

        $teamIds = $this->getTeamIds();

        if (empty($teamIds)) {
            return Project::where('created_by', $this->id)->get();
        }

        return Project::whereHas('teams', function ($query) use ($teamIds) {
            $query->whereIn('teams.id', $teamIds);
        })->orWhere('created_by', $this->id)->get();
    }

    public function getVisibleProjectIds(): array
    {
        return $this->getVisibleProjects()->pluck('id')->toArray();
    }

    // ========== PHASE METHODS ==========

    public function getVisiblePhases(): Collection
    {
        if ($this->isDirector()) {
            return Phase::all();
        }

        $projectIds = $this->getVisibleProjectIds();

        if (empty($projectIds)) {
            return collect();
        }

        return Phase::whereIn('project_id', $projectIds)->get();
    }

    public function getVisiblePhaseIds(): array
    {
        return $this->getVisiblePhases()->pluck('id')->toArray();
    }

    // ========== TASK METHODS ==========

    public function getVisibleTasks(): Collection
    {
        if ($this->isDirector()) {
            return Task::all();
        }

        if ($this->isTeamLeader()) {
            $teamIds = $this->getTeamIds();
            $teamMemberIds = User::whereHas('teams', function ($query) use ($teamIds) {
                $query->whereIn('teams.id', $teamIds);
            })->pluck('id')->toArray();

            $teamMemberIds[] = $this->id;
            $teamMemberIds = array_unique($teamMemberIds);

            return Task::whereIn('assigned_to', $teamMemberIds)
                ->orWhere('created_by', $this->id)
                ->get();
        }

        return Task::where('assigned_to', $this->id)
            ->orWhere('created_by', $this->id)
            ->orWhereHas('assignedUsers', function ($q) {
                $q->where('user_id', $this->id);
            })
            ->get();
    }

    public function getVisibleTaskIds(): array
    {
        return $this->getVisibleTasks()->pluck('id')->toArray();
    }
}
