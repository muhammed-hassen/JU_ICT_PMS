<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TeamLeaderController extends Controller
{
    public function index(): View
    {
        $teamLeaders = User::role('Team Leader')
            ->with('ledTeams')
            ->orderBy('name')
            ->paginate(10);

        return view('admin.organization.team-leaders.index', compact('teamLeaders'));
    }

    public function create(): View
    {
        return view('admin.organization.team-leaders.create', $this->formData(new User));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateTeamLeader($request);

        DB::transaction(function () use ($validated): void {
            $teamLeader = User::query()->create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'email_verified_at' => now(),
            ]);

            $teamLeader->syncRoles(['Team Leader']);
            $this->syncLedTeams($teamLeader, $validated['team_ids'] ?? []);
        });

        return redirect()
            ->route('admin.organization.team-leaders.index')
            ->with('status', 'Team leader created.');
    }

    public function show(User $teamLeader): View
    {
        $this->ensureRole($teamLeader);

        return view('admin.organization.team-leaders.show', [
            'teamLeader' => $teamLeader->load(['ledTeams.members', 'roles']),
        ]);
    }

    public function edit(User $teamLeader): View
    {
        $this->ensureRole($teamLeader);

        return view('admin.organization.team-leaders.edit', $this->formData($teamLeader->load('ledTeams')));
    }

    public function update(Request $request, User $teamLeader): RedirectResponse
    {
        $this->ensureRole($teamLeader);

        $validated = $this->validateTeamLeader($request, $teamLeader);

        DB::transaction(function () use ($teamLeader, $validated): void {
            $teamLeader->update(array_filter([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'] ?? null,
            ], fn ($value) => $value !== null));

            $teamLeader->syncRoles(['Team Leader']);
            $this->syncLedTeams($teamLeader, $validated['team_ids'] ?? []);
        });

        return redirect()
            ->route('admin.organization.team-leaders.show', $teamLeader)
            ->with('status', 'Team leader updated.');
    }

    public function destroy(User $teamLeader): RedirectResponse
    {
        $this->ensureRole($teamLeader);

        try {
            DB::transaction(function () use ($teamLeader): void {
                $teamLeader->ledTeams()->update(['team_leader_id' => null]);
                $teamLeader->delete();
            });
        } catch (QueryException) {
            return redirect()
                ->route('admin.organization.team-leaders.index')
                ->with('error', 'This team leader is still referenced and cannot be deleted.');
        }

        return redirect()
            ->route('admin.organization.team-leaders.index')
            ->with('status', 'Team leader deleted.');
    }

    protected function formData(User $teamLeader): array
    {
        return [
            'teamLeader' => $teamLeader,
            'teams' => Team::query()->with('teamLeader')->orderBy('name')->get(),
        ];
    }

    protected function validateTeamLeader(Request $request, ?User $teamLeader = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($teamLeader?->id),
            ],
            'password' => [$teamLeader?->exists ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'team_ids' => ['nullable', 'array'],
            'team_ids.*' => ['integer', Rule::exists('teams', 'id')],
        ]);
    }

    protected function syncLedTeams(User $teamLeader, array $teamIds): void
    {
        $teamLeader->ledTeams()->whereNotIn('id', $teamIds)->update(['team_leader_id' => null]);

        if ($teamIds) {
            Team::query()
                ->whereIn('id', $teamIds)
                ->update(['team_leader_id' => $teamLeader->id]);
        }
    }

    protected function ensureRole(User $teamLeader): void
    {
        abort_unless($teamLeader->hasRole('Team Leader'), 404);
    }
}
