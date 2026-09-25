@extends('adminlte::page')

@section('title', 'Create User')

@section('content_header')
    <h1>Create New User</h1>
@stop

<link rel="stylesheet" href="{{ asset('css/ju-custom.css') }}">

@section('content')

<div class="row">
    <div class="col-md-6 offset-md-3">

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">User Information</h3>
            </div>
        @if ($errors->any())
    <div class="alert alert-danger" id="errorAlert">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
            <form action="{{ route('users.store') }}" method="POST">
                @csrf

                <div class="card-body">

                    {{-- Name --}}
                    <div class="form-group">
                        <label>Name</label>
                       <input type="text" name="name" value="{{ old('name') }}" class="form-control">

                    </div>

                    {{-- Email --}}
                    <div class="form-group">
                        <label>Email</label>
                       <input type="email" name="email" value="{{ old('email') }}" class="form-control">

                    </div>

                    {{-- Password --}}
                    <div class="form-group">
    <label>Password</label>

    <div class="input-group">
        <input type="password" name="password" id="password"
               class="form-control" required>

        <div class="input-group-append">
            <span class="input-group-text" onclick="togglePassword()" style="cursor:pointer;">
                <i class="fas fa-eye" id="eyeIcon"></i>
            </span>
        </div>
    </div>

    @error('password')
        <small class="text-danger">{{ $message }}</small>
    @enderror
</div>

                    {{-- Role --}}
                    <div class="form-group">
    <label>Role</label>

    <select name="role_id" class="form-control">
        @foreach($roles as $role)
            <option value="{{ $role->id }}"
                {{ isset($user) && $user->role_id == $role->id ? 'selected' : '' }}>
                {{ $role->name }}
            </option>
        @endforeach
    </select>
</div>

                </div>

                <div class="card-footer text-right">
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        Save User
                    </button>
                </div>

            </form>

        </div>

    </div>
</div>

@stop
@push('js')
<script>
function togglePassword() {
    const password = document.getElementById('password');
    const icon = document.getElementById('eyeIcon');

    if (password.type === "password") {
        password.type = "text";
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        password.type = "password";
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>
@endpush
@push('js')
<script>
    setTimeout(function () {
        let alert = document.getElementById('errorAlert');
        if (alert) {
            alert.style.transition = "opacity 0.5s ease";
            alert.style.opacity = "0";

            setTimeout(() => {
                alert.remove();
            }, 500);
        }
    }, 4000); // stays 4 seconds
</script>
@endpush
