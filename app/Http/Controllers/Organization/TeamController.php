<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TeamController extends Controller
{
    public function index(): View
    {
        $teams = Team::query()
            ->with(['teamLeader', 'parentTeam'])
            ->withCount(['members', 'childTeams', 'projects'])
            ->orderBy('parent_team_id')
            ->orderBy('name')
            ->paginate(10);

        return view('admin.organization.teams.index', compact('teams'));
    }

    public function create(): View
    {
        return view('admin.organization.teams.create', $this->formData(new Team));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateTeam($request);

        $team = Team::query()->create(collect($validated)->except('member_ids')->all());
        $team->members()->sync($validated['member_ids'] ?? []);

        return redirect()
            ->route('admin.organization.teams.index')
            ->with('status', 'Team created.');
    }

    public function show(Team $team): View
    {
        return view('admin.organization.teams.show', [
            'team' => $team->load(['teamLeader', 'parentTeam', 'childTeams.teamLeader', 'members.roles', 'projects']),
        ]);
    }

    public function edit(Team $team): View
    {
        return view('admin.organization.teams.edit', $this->formData($team->load('members')));
    }

    public function update(Request $request, Team $team): RedirectResponse
    {
        $validated = $this->validateTeam($request, $team);

        $team->update(collect($validated)->except('member_ids')->all());
        $team->members()->sync($validated['member_ids'] ?? []);

        return redirect()
            ->route('admin.organization.teams.show', $team)
            ->with('status', 'Team updated.');
    }

    public function destroy(Team $team): RedirectResponse
    {
        if ($team->childTeams()->exists() || $team->members()->exists() || $team->projects()->exists()) {
            return redirect()
                ->route('admin.organization.teams.index')
                ->with('error', 'This team still has child teams, members, or projects.');
        }

        try {
            $team->delete();
        } catch (QueryException) {
            return redirect()
                ->route('admin.organization.teams.index')
                ->with('error', 'This team is still referenced and cannot be deleted.');
        }

        return redirect()
            ->route('admin.organization.teams.index')
            ->with('status', 'Team deleted.');
    }

    protected function formData(Team $team): array
    {
        return [
            'team' => $team,
            'leaders' => User::role('Team Leader')->orderBy('name')->get(),
            'parentTeams' => Team::query()
                ->when($team->exists, fn ($query) => $query->whereKeyNot($team->id))
                ->orderBy('name')
                ->get(),
            'members' => User::role('Team Member')->orderBy('name')->get(),
        ];
    }

    protected function validateTeam(Request $request, ?Team $team = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('teams', 'name')->ignore($team?->id),
            ],
            'description' => ['nullable', 'string'],
            'team_leader_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'parent_team_id' => [
                'nullable',
                'integer',
                Rule::exists('teams', 'id'),
                Rule::notIn([$team?->id]),
            ],
            'member_ids' => ['nullable', 'array'],
            'member_ids.*' => ['integer', Rule::exists('users', 'id')],
        ]);
    }
}
