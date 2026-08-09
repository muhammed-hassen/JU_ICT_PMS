<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DirectorController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        if (! $user->hasPermissionTo('view-directors')) {
            abort(403, 'You do not have permission to view directors.');
        }

        $directors = User::whereHas('roles', function ($query) {
            $query->where('name', 'ICT Director');
        })->withCount('ledTeams')->orderBy('name')->paginate(10);

        return view('admin.organization.directors.index', compact('directors'));
    }

    public function create(): View
    {
        $user = auth()->user();

        if (! $user->hasPermissionTo('manage-directors')) {
            abort(403, 'You do not have permission to create directors.');
        }

        return view('admin.organization.directors.create', ['director' => new User]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        if (! $user->hasPermissionTo('manage-directors')) {
            abort(403, 'You do not have permission to create directors.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $director = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'email_verified_at' => now(),
        ]);

        $director->assignRole('ICT Director');

        return redirect()->route('admin.organization.directors.index')
            ->with('success', 'Director created successfully.');
    }

    public function show(User $director): View
    {
        $user = auth()->user();

        if (! $user->hasPermissionTo('view-directors')) {
            abort(403, 'You do not have permission to view directors.');
        }

        return view('admin.organization.directors.show', [
            'director' => $director->load(['ledTeams.members', 'roles']),
        ]);
    }

    public function edit(User $director): View
    {
        $user = auth()->user();

        if (! $user->hasPermissionTo('manage-directors')) {
            abort(403, 'You do not have permission to edit directors.');
        }

        return view('admin.organization.directors.edit', compact('director'));
    }

    public function update(Request $request, User $director): RedirectResponse
    {
        $user = auth()->user();

        if (! $user->hasPermissionTo('manage-directors')) {
            abort(403, 'You do not have permission to update directors.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$director->id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if (! empty($validated['password'])) {
            $updateData['password'] = bcrypt($validated['password']);
        }

        $director->update($updateData);
        $director->syncRoles(['ICT Director']);

        return redirect()->route('admin.organization.directors.show', $director)
            ->with('success', 'Director updated successfully.');
    }

    public function destroy(User $director): RedirectResponse
    {
        $user = auth()->user();

        if (! $user->hasPermissionTo('manage-directors')) {
            abort(403, 'You do not have permission to delete directors.');
        }

        if ($director->ledTeams()->exists()) {
            return redirect()->route('admin.organization.directors.index')
                ->with('error', 'Cannot delete director who is a team leader.');
        }

        $director->delete();

        return redirect()->route('admin.organization.directors.index')
            ->with('success', 'Director deleted successfully.');
    }
}
