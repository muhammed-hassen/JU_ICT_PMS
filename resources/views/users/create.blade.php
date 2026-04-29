@extends('layouts.app')
@section('title', 'Create User')

@section('content')
<div class="max-w-2xl mx-auto bg-white rounded-xl shadow-sm border p-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800"><i class="fas fa-user-plus text-indigo-600 mr-2"></i>Create New User</h1>
        <p class="text-sm text-gray-500 mt-1">Fill in the details below</p>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('users.store') }}" method="POST" class="space-y-5">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
            <input type="text" name="name" value="{{ old('name') }}" required
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <div class="relative">
                <input type="password" name="password" id="password" required
                       class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                <button type="button" onclick="togglePassword('password', this)"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            <p class="text-xs text-gray-500 mt-1">Min 8 characters, must include letters and numbers.</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
            <select name="role_id" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                <option value="">-- Select Role --</option>
                @foreach($roles as $role)
                    <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                        {{ $role->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex gap-3 pt-4">
            <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg shadow transition flex items-center gap-2">
                <i class="fas fa-save"></i> Save User
            </button>
            <a href="{{ route('users.index') }}"
               class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg transition">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
