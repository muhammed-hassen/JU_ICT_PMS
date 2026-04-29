@extends('layouts.app')
@section('title', 'Users List')

@section('content')
<div class="bg-white rounded-xl shadow-sm border p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Users</h1>
            <p class="text-sm text-gray-500 mt-1">Manage users and their roles</p>
        </div>
        <a href="{{ route('users.create') }}"
           class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 shadow transition">
            <i class="fas fa-plus"></i> Create User
        </a>
    </div>

    {{-- Search --}}
    <form action="{{ route('users.index') }}" method="GET" class="mb-6 flex gap-2">
        <div class="relative flex-1">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search by name or email"
                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
        </div>
        <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-5 py-2 rounded-lg transition">
            Search
        </button>
    </form>

    {{-- Table --}}
    <div class="overflow-x-auto rounded-lg border">
        <table class="w-full text-left">
            <thead class="bg-gray-50 text-gray-600 text-sm uppercase">
                <tr>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Role</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($users as $user)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $user->name }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $user->email }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-block bg-indigo-100 text-indigo-700 text-xs font-semibold px-2 py-1 rounded">
                            {{ $user->role->name ?? 'No Role' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('users.edit', $user->id) }}"
                           class="inline-flex items-center gap-1 bg-yellow-500 hover:bg-yellow-600 text-white text-sm px-3 py-1.5 rounded-md transition">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="inline delete-form">
                            @csrf
                            @method('DELETE')
                            <button type="button" onclick="confirmDelete(this)"
                                    class="inline-flex items-center gap-1 bg-red-500 hover:bg-red-600 text-white text-sm px-3 py-1.5 rounded-md transition">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-8 text-center text-gray-400">
                        <i class="fas fa-inbox text-3xl mb-2 block"></i>
                        No users found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    function confirmDelete(btn) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This user will be permanently deleted!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                btn.closest('form').submit();
            }
        });
    }
</script>
@endsection
