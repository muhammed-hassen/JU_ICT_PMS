@extends('adminlte::page')

@section('title', 'Edit User')

@section('content_header')
    <h1>Edit User</h1>
@stop

<link rel="stylesheet" href="{{ asset('css/ju-custom.css') }}">

@section('content')

<div class="row justify-content-center">
    <div class="col-md-6">

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Update User</h3>
            </div>

            <form method="POST" action="{{ route('users.update', $user->id) }}">
                @csrf
                @method('PUT')

                <div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card-body">
                    {{-- Name --}}
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name"
                               value="{{ old('name', $user->name) }}"
                               class="form-control @error('name') is-invalid @enderror">

                        @error('name')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email"
                               value="{{ old('email', $user->email) }}"
                               class="form-control @error('email') is-invalid @enderror">

                        @error('email')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="form-group">
                        <label>Password (leave empty if not changing)</label>
                        <input type="password" name="password"
                               class="form-control @error('password') is-invalid @enderror">

                        @error('password')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Role --}}
                    <div class="form-group">
                        <label>Role</label>
                        <select name="role_id" class="form-control">
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}"
                                    {{ $user->role_id == $role->id ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                </div>

                <div class="card-footer">
                    <button class="btn btn-primary">
                        Update User
                    </button>

                    <a href="{{ route('users.index') }}" class="btn btn-secondary">
                        Cancel
                    </a>
                </div>

            </form>

        </div>
        </div>
    </div>
</div>
    </div>
</div>

@stop
<script>
    setTimeout(function () {
        let alert = document.querySelector('.alert');
        if (alert) {
            alert.style.transition = "0.5s";
            alert.style.opacity = 0;

            setTimeout(() => alert.remove(), 500);
        }
    }, 2000); // disappears after  2 seconds
</script>
