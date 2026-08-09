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
     * Show form to create a new member
     */
    public function create(): View
    {
        $user = auth()->user();

        if (! $user->hasPermissionTo('manage-members')) {
            abort(403, 'You do not have permission to create members.');
        }

        $teams = Team::all();
        $member = new User;

        return view('admin.organization.members.create', compact('teams', 'member'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        if (! $user->hasPermissionTo('manage-members')) {
            abort(403, 'You do not have permission to create members.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'team_ids' => 'nullable|array',
            'team_ids.*' => 'exists:teams,id',
        ]);

        $member = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'email_verified_at' => now(),
        ]);

        $member->assignRole('Team Member');

        if (! empty($validated['team_ids'])) {
            $member->teams()->sync($validated['team_ids']);
        }

        return redirect()->route('admin.organization.members.index')
            ->with('success', 'Member created successfully.');
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

        return view('admin.organization.members.edit', compact('member', 'teams'));
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
