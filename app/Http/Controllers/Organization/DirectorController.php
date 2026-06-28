<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DirectorController extends Controller
{
    public function index(): View
    {
        $directors = User::role('ICT Director')
            ->withCount('ledTeams')
            ->orderBy('name')
            ->paginate(10);

        return view('admin.organization.directors.index', compact('directors'));
    }

    public function create(): View
    {
        return view('admin.organization.directors.create', ['director' => new User]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateDirector($request);

        $director = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'email_verified_at' => now(),
        ]);

        $director->syncRoles(['ICT Director']);

        return redirect()
            ->route('admin.organization.directors.index')
            ->with('status', 'Director created.');
    }

    public function show(User $director): View
    {
        $this->ensureRole($director);

        return view('admin.organization.directors.show', [
            'director' => $director->load(['ledTeams.members', 'roles']),
        ]);
    }

    public function edit(User $director): View
    {
        $this->ensureRole($director);

        return view('admin.organization.directors.edit', compact('director'));
    }

    public function update(Request $request, User $director): RedirectResponse
    {
        $this->ensureRole($director);

        $validated = $this->validateDirector($request, $director);

        $director->update(array_filter([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'] ?? null,
        ], fn ($value) => $value !== null));

        $director->syncRoles(['ICT Director']);

        return redirect()
            ->route('admin.organization.directors.show', $director)
            ->with('status', 'Director updated.');
    }

    public function destroy(User $director): RedirectResponse
    {
        $this->ensureRole($director);

        if ($director->ledTeams()->exists()) {
            return redirect()
                ->route('admin.organization.directors.index')
                ->with('error', 'This director is assigned as a team leader and cannot be deleted.');
        }

        try {
            $director->delete();
        } catch (QueryException) {
            return redirect()
                ->route('admin.organization.directors.index')
                ->with('error', 'This director is still referenced and cannot be deleted.');
        }

        return redirect()
            ->route('admin.organization.directors.index')
            ->with('status', 'Director deleted.');
    }

    protected function validateDirector(Request $request, ?User $director = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($director?->id),
            ],
            'password' => [$director?->exists ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    protected function ensureRole(User $director): void
    {
        abort_unless($director->hasRole('ICT Director'), 404);
    }
}
