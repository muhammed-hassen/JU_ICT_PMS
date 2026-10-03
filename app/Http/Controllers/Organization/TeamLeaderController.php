<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeamLeaderController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        if (! $user->hasPermissionTo('view-team-leaders')) {
            abort(403, 'You do not have permission to view team leaders.');
        }

        $teamLeaders = User::whereHas('roles', function ($query) {
            $query->where('name', 'Team Leader');
        })->with('ledTeams')->orderBy('name')->paginate(10);

        return view('admin.organization.team-leaders.index', compact('teamLeaders'));
    }

    public function create(): View
    {
        $user = auth()->user();

        if (! $user->hasPermissionTo('manage-team-leaders')) {
            abort(403, 'You do not have permission to create team leaders.');
        }

        $teams = Team::with('teamLeader')->orderBy('name')->get();

        // Only offer users who are not already Team Leaders
        $eligibleUsers = User::whereDoesntHave('roles', fn ($q) => $q->where('name', 'Team Leader'))
            ->orderBy('name')
            ->get();

        $teamLeader = new User;

        return view('admin.organization.team-leaders.create', compact('teams', 'teamLeader', 'eligibleUsers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        if (! $user->hasPermissionTo('manage-team-leaders')) {
            abort(403, 'You do not have permission to create team leaders.');
        }

        $validated = $request->validate([
            'user_id'    => 'required|exists:users,id',
            'team_ids'   => 'nullable|array',
            'team_ids.*' => 'exists:teams,id',
        ]);

        DB::transaction(function () use ($validated) {
            $teamLeader = User::findOrFail($validated['user_id']);

            // Give the Team Leader role if they don't already have it
            if (! $teamLeader->hasRole('Team Leader')) {
                $teamLeader->assignRole('Team Leader');
            }

            if (! empty($validated['team_ids'])) {
                Team::whereIn('id', $validated['team_ids'])->update(['team_leader_id' => $teamLeader->id]);
            }
        });

        return redirect()->route('admin.organization.team-leaders.index')
            ->with('success', 'Team leader assigned successfully.');
    }

    public function show(User $teamLeader): View
    {
        $user = auth()->user();

        if (! $user->hasPermissionTo('view-team-leaders')) {
            abort(403, 'You do not have permission to view team leaders.');
        }

        return view('admin.organization.team-leaders.show', [
            'teamLeader' => $teamLeader->load(['ledTeams.members', 'roles']),
        ]);
    }

    public function edit(User $teamLeader): View
    {
        $user = auth()->user();

        if (! $user->hasPermissionTo('manage-team-leaders')) {
            abort(403, 'You do not have permission to edit team leaders.');
        }

        $teams = Team::with('teamLeader')->orderBy('name')->get();
        $selectedTeams = $teamLeader->ledTeams->pluck('id')->toArray();

        return view('admin.organization.team-leaders.edit', compact('teamLeader', 'teams', 'selectedTeams'));
    }

    public function update(Request $request, User $teamLeader): RedirectResponse
    {
        $user = auth()->user();

        if (! $user->hasPermissionTo('manage-team-leaders')) {
            abort(403, 'You do not have permission to update team leaders.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$teamLeader->id,
            'password' => 'nullable|string|min:8|confirmed',
            'team_ids' => 'nullable|array',
            'team_ids.*' => 'exists:teams,id',
        ]);

        DB::transaction(function () use ($teamLeader, $validated) {
            $updateData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
            ];

            if (! empty($validated['password'])) {
                $updateData['password'] = bcrypt($validated['password']);
            }

            $teamLeader->update($updateData);
            $teamLeader->syncRoles(['Team Leader']);

            Team::where('team_leader_id', $teamLeader->id)->update(['team_leader_id' => null]);
            if (! empty($validated['team_ids'])) {
                Team::whereIn('id', $validated['team_ids'])->update(['team_leader_id' => $teamLeader->id]);
            }
        });

        return redirect()->route('admin.organization.team-leaders.show', $teamLeader)
            ->with('success', 'Team leader updated successfully.');
    }

    public function destroy(User $teamLeader): RedirectResponse
    {
        $user = auth()->user();

        if (! $user->hasPermissionTo('manage-team-leaders')) {
            abort(403, 'You do not have permission to delete team leaders.');
        }

        DB::transaction(function () use ($teamLeader) {
            Team::where('team_leader_id', $teamLeader->id)->update(['team_leader_id' => null]);
            $teamLeader->delete();
        });

        return redirect()->route('admin.organization.team-leaders.index')
            ->with('success', 'Team leader deleted successfully.');
    }
}
