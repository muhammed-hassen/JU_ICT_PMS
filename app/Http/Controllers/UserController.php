<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of users (with search)
     */
    public function index(Request $request)
{
    $search = $request->input('search');

    $users = User::with('role')
        ->when($search, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        })
        //->latest()//
        ->paginate(10);
        //->withQueryString();//

    return view('users.index', compact('users', 'search'));
}

    /**
     * Show create form
     */
    public function create()
    {
        $roles = Role::all();
        return view('users.create', compact('roles'));
    }

    /**
     * Store new user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
    'name'     => 'required|string|max:255',
    'email'    => 'required|email|unique:users,email',
    'password' => ['required', 'string', 'min:8', 'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/'],
    'role_id'  => 'required|exists:roles,id',
], [
    'password.regex' => 'Password must contain both letters and numbers.',
]);

$validated['password'] = bcrypt($validated['password']);
User::create($validated);

return redirect()->route('users.index')->with('success', 'User created successfully');
    }

    public function show(string $id) {}

    /**
     * Show edit form
     */
   public function edit(User $user)
{
    $roles = Role::all();

    return view('users.edit', compact('user', 'roles'));

}
    /**
     * Update user
     */
    public function update(Request $request, User $user)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $user->id,
        'password' => ['nullable', 'string', 'min:8', 'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/'],
        'role_id' => 'required|exists:roles,id',
    ], [
        'password.regex' => 'Password must contain both letters and numbers.',
    ]);

    if ($request->filled('password')) {
        $validated['password'] = bcrypt($validated['password']);
    } else {
        unset($validated['password']);
    }

    $user->update($validated);

    return redirect()->route('users.index')
        ->with('success', 'User updated successfully');
}

    /**
     * Delete user
     */
   public function destroy(User $user)
{
    $user->delete();

    return redirect()->route('users.index')
        ->with('success', 'User deleted successfully');
}
}
