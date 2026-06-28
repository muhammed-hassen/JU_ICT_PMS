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

class MemberController extends Controller
{
    public function index(): View
    {
        $members = User::role('Team Member')
            ->with('teams')
            ->orderBy('name')
            ->paginate(10);

        return view('admin.organization.members.index', compact('members'));
    }

    public function create(): View
    {
        return view('admin.organization.members.create', $this->formData(new User));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateMember($request);

        $member = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'email_verified_at' => now(),
        ]);

        $member->syncRoles(['Team Member']);
        $member->teams()->sync($validated['team_ids'] ?? []);

        return redirect()
            ->route('admin.organization.members.index')
            ->with('status', 'Member created.');
    }

    public function show(User $member): View
    {
        $this->ensureRole($member);

        return view('admin.organization.members.show', [
            'member' => $member->load(['teams.teamLeader', 'roles']),
        ]);
    }

    public function edit(User $member): View
    {
        $this->ensureRole($member);

        return view('admin.organization.members.edit', $this->formData($member->load('teams')));
    }

    public function update(Request $request, User $member): RedirectResponse
    {
        $this->ensureRole($member);

        $validated = $this->validateMember($request, $member);

        $member->update(array_filter([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'] ?? null,
        ], fn ($value) => $value !== null));

        $member->syncRoles(['Team Member']);
        $member->teams()->sync($validated['team_ids'] ?? []);

        return redirect()
            ->route('admin.organization.members.show', $member)
            ->with('status', 'Member updated.');
    }

    public function destroy(User $member): RedirectResponse
    {
        $this->ensureRole($member);

        try {
            $member->teams()->detach();
            $member->delete();
        } catch (QueryException) {
            return redirect()
                ->route('admin.organization.members.index')
                ->with('error', 'This member is still referenced and cannot be deleted.');
        }

        return redirect()
            ->route('admin.organization.members.index')
            ->with('status', 'Member deleted.');
    }

    protected function formData(User $member): array
    {
        return [
            'member' => $member,
            'teams' => Team::query()->orderBy('name')->get(),
        ];
    }

    protected function validateMember(Request $request, ?User $member = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($member?->id),
            ],
            'password' => [$member?->exists ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'team_ids' => ['nullable', 'array'],
            'team_ids.*' => ['integer', Rule::exists('teams', 'id')],
        ]);
    }

    protected function ensureRole(User $member): void
    {
        abort_unless($member->hasRole('Team Member'), 404);
    }
}
