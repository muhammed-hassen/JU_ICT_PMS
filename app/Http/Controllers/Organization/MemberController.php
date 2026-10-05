<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        if (! $user->hasPermissionTo('view-members')) {
            abort(403, 'You do not have permission to view members.');
        }

        $query = User::query()
            ->with('teams')
            ->orderBy('name');

        if (! $user->isDirector()) {
            $teamIds = $user->getTeamIds();
            if (empty($teamIds)) {
                $members = User::query()->whereRaw('1 = 0')->paginate(10);

                return view('admin.organization.members.index', compact('members'));
            }
            $query->whereHas('teams', function ($q) use ($teamIds) {
                $q->whereIn('teams.id', $teamIds);
            });
        }

        $members = $query->paginate(10);

        return view('admin.organization.members.index', compact('members'));
    }

    /**
     * Show form to add an existing user as a team member.
     */
    public function create(): View
    {
        $user = auth()->user();

        if (! $user->hasPermissionTo('manage-members')) {
            abort(403, 'You do not have permission to manage members.');
        }

        // Offer every user who does not already have the Team Member role,
        // or users who have it but can still be placed in additional teams.
        // We show ALL non-admin users so the admin can pick any existing account.
        $existingUsers = User::orderBy('name')
            ->whereDoesntHave('roles', function ($q) {
                $q->whereIn('name', ['ICT Director', 'System Administrator', 'Super Admin']);
            })
            ->with('teams')
            ->get();

        $teams = Team::orderBy('name')->get();

        return view('admin.organization.members.create', compact('existingUsers', 'teams'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        if (! $user->hasPermissionTo('manage-members')) {
            abort(403, 'You do not have permission to manage members.');
        }

        $validated = $request->validate([
            'user_id'    => 'required|exists:users,id',
            'team_ids'   => 'nullable|array',
            'team_ids.*' => 'exists:teams,id',
        ]);

        $member = User::findOrFail($validated['user_id']);

        // Ensure the user carries the Team Member role.
        if (! $member->hasRole('Team Member')) {
            $member->assignRole('Team Member');
        }

        // Merge the newly selected teams with any the user already belongs to.
        if (! empty($validated['team_ids'])) {
            $member->teams()->syncWithoutDetaching($validated['team_ids']);
        }

        return redirect()->route('admin.organization.members.index')
            ->with('success', $member->name.' has been added as a team member.');
    }

    public function show(User $member): View
    {
        $user = auth()->user();

        if (! $user->hasPermissionTo('view-members')) {
            abort(403, 'You do not have permission to view members.');
        }

        if (! $user->isDirector()) {
            $teamIds = $user->getTeamIds();
            $userTeams = $member->teams()->pluck('teams.id')->toArray();
            if (empty(array_intersect($teamIds, $userTeams))) {
                abort(403, 'You do not have permission to view this member.');
            }
        }

        $member->load('teams');

        return view('admin.organization.members.show', compact('member'));
    }

    public function edit(User $member): View
    {
        $user = auth()->user();

        if (! $user->hasPermissionTo('manage-members')) {
            abort(403, 'You do not have permission to edit members.');
        }

        $teams = Team::all();
        $member->load('teams');

        $existingUsers = User::orderBy('name')
            ->whereDoesntHave('roles', function ($q) {
                $q->whereIn('name', ['ICT Director', 'System Administrator', 'Super Admin']);
            })
            ->with('teams')
            ->get();

        return view('admin.organization.members.edit', compact('member', 'teams', 'existingUsers'));
    }

    public function update(Request $request, User $member): RedirectResponse
    {
        $user = auth()->user();

        if (! $user->hasPermissionTo('manage-members')) {
            abort(403, 'You do not have permission to update members.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$member->id,
            'password' => 'nullable|string|min:8|confirmed',
            'team_ids' => 'nullable|array',
            'team_ids.*' => 'exists:teams,id',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if (! empty($validated['password'])) {
            $updateData['password'] = bcrypt($validated['password']);
        }

        $member->update($updateData);

        if (isset($validated['team_ids'])) {
            $member->teams()->sync($validated['team_ids']);
        } else {
            $member->teams()->detach();
        }

        return redirect()->route('admin.organization.members.index')
            ->with('success', 'Member updated successfully.');
    }

    public function destroy(User $member): RedirectResponse
    {
        $user = auth()->user();

        if (! $user->hasPermissionTo('manage-members')) {
            abort(403, 'You do not have permission to delete members.');
        }

        $member->teams()->detach();
        $member->delete();

        return redirect()->route('admin.organization.members.index')
            ->with('success', 'Member deleted successfully.');
    }
}
