@extends('adminlte::auth.passwords.email')

@section('title', 'Reset Password')

@section('content')
<div class="card">
    <div class="card-body">
        <p>Enter your email to receive reset link</p>

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <input type="email" name="email" class="form-control" required>

            <button class="btn btn-primary mt-2">
                Send Reset Link
            </button>
        </form>
    </div>
</div>
@stop
