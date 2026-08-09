<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TeamController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        if (! $user->hasPermissionTo('view-teams')) {
            abort(403, 'You do not have permission to view teams.');
        }

        $query = Team::query()
            ->with(['teamLeader', 'parentTeam'])
            ->withCount(['members', 'childTeams', 'projects']);

        if (! $user->isDirector()) {
            $teamIds = $user->getTeamIds();

            if (empty($teamIds)) {
                $teams = Team::query()->whereRaw('1 = 0')->paginate(10);

                return view('admin.organization.teams.index', compact('teams'));
            }

            $query->whereIn('id', $teamIds);
        }

        $teams = $query->orderBy('parent_team_id')
            ->orderBy('name')
            ->paginate(10);

        return view('admin.organization.teams.index', compact('teams'));
    }

    public function show(Team $team): View
    {
        $user = auth()->user();

        if (! $user->hasPermissionTo('view-teams')) {
            abort(403, 'You do not have permission to view teams.');
        }

        if (! $user->isDirector()) {
            $teamIds = $user->getTeamIds();
            if (! in_array($team->id, $teamIds)) {
                abort(403, 'You do not have permission to view this team.');
            }
        }

        $team->load(['teamLeader', 'parentTeam', 'childTeams', 'members', 'projects']);

        return view('admin.organization.teams.show', compact('team'));
    }

    /**
     * Show form to create a new team
     */
    public function create(): View
    {
        $user = auth()->user();

        if (! $user->hasPermissionTo('manage-teams')) {
            abort(403, 'You do not have permission to create teams.');
        }

        $leaders = User::whereHas('roles', function ($query) {
            $query->where('name', 'Team Leader');
        })->get();

        $members = User::all();
        $parentTeams = Team::all();
        $team = new Team;

        return view('admin.organization.teams.create', compact('leaders', 'members', 'parentTeams', 'team'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        if (! $user->hasPermissionTo('manage-teams')) {
            abort(403, 'You do not have permission to create teams.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:teams',
            'description' => 'nullable|string',
            'team_leader_id' => 'nullable|exists:users,id',
            'parent_team_id' => 'nullable|exists:teams,id',
            'member_ids' => 'nullable|array',
            'member_ids.*' => 'exists:users,id',
        ]);

        $team = Team::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'team_leader_id' => $validated['team_leader_id'] ?? null,
            'parent_team_id' => $validated['parent_team_id'] ?? null,
        ]);

        if (! empty($validated['member_ids'])) {
            $team->members()->sync($validated['member_ids']);
        }

        return redirect()->route('admin.organization.teams.index')
            ->with('success', 'Team created successfully.');
    }

    public function edit(Team $team): View
    {
        $user = auth()->user();

        if (! $user->hasPermissionTo('manage-teams')) {
            abort(403, 'You do not have permission to edit teams.');
        }

        $leaders = User::whereHas('roles', function ($query) {
            $query->where('name', 'Team Leader');
        })->get();

        $members = User::all();
        $parentTeams = Team::where('id', '!=', $team->id)->get();

        return view('admin.organization.teams.edit', compact('team', 'leaders', 'members', 'parentTeams'));
    }

    public function update(Request $request, Team $team): RedirectResponse
    {
        $user = auth()->user();

        if (! $user->hasPermissionTo('manage-teams')) {
            abort(403, 'You do not have permission to update teams.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('teams')->ignore($team->id)],
            'description' => 'nullable|string',
            'team_leader_id' => 'nullable|exists:users,id',
            'parent_team_id' => 'nullable|exists:teams,id',
            'member_ids' => 'nullable|array',
            'member_ids.*' => 'exists:users,id',
        ]);

        $team->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'team_leader_id' => $validated['team_leader_id'] ?? null,
            'parent_team_id' => $validated['parent_team_id'] ?? null,
        ]);

        if (isset($validated['member_ids'])) {
            $team->members()->sync($validated['member_ids']);
        } else {
            $team->members()->detach();
        }

        return redirect()->route('admin.organization.teams.index')
            ->with('success', 'Team updated successfully.');
    }

    public function destroy(Team $team): RedirectResponse
    {
        $user = auth()->user();

        if (! $user->hasPermissionTo('manage-teams')) {
            abort(403, 'You do not have permission to delete teams.');
        }

        if ($team->childTeams()->exists() || $team->members()->exists() || $team->projects()->exists()) {
            return back()->with('error', 'Cannot delete team with existing relationships.');
        }

        $team->delete();

        return redirect()->route('admin.organization.teams.index')
            ->with('success', 'Team deleted successfully.');
    }
}
