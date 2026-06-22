<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index()
    {
       $teams = Team::with([
    'teamLeader',
    'parentTeam',
    'members'
])->get();

        return view('admin.teams.index', compact('teams'));
    }

    public function create()
    {
        $users = User::all();
        $teams = Team::all();

        return view('admin.teams.create', compact('users', 'teams'));
    }

    public function store(Request $request)
{
    $team = Team::create([
        'name' => $request->name,
        'description' => $request->description,
        'team_leader_id' => $request->team_leader_id,
        'parent_team_id' => $request->parent_team_id,
    ]);

    if ($request->members) {
        $team->members()->attach($request->members);
    }

    return redirect()->route('admin.teams.index')
        ->with('success', 'Team created successfully.');
}
    public function edit(Team $team)
    {
        $users = User::all();
        $teams = Team::where('id', '!=', $team->id)->get();

       $selectedMembers = $team->members->pluck('id')->toArray();

return view(
    'admin.teams.edit',
    compact('team', 'users', 'teams', 'selectedMembers')
);
    }

    public function update(Request $request, Team $team)
{
    $team->update([
        'name' => $request->name,
        'description' => $request->description,
        'team_leader_id' => $request->team_leader_id,
        'parent_team_id' => $request->parent_team_id,
    ]);

    if ($request->members) {
        $team->members()->sync($request->members);
    } else {
        $team->members()->detach();
    }

    return redirect()->route('admin.teams.index')
        ->with('success', 'Team updated successfully.');
}
    public function destroy(Team $team)
    {
        $team->delete();

        return redirect()
            ->route('admin.teams.index')
            ->with('success', 'Team deleted successfully.');
    }
}
