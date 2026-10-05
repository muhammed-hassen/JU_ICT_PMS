@extends('layouts.auth')

@section('title', 'Confirm password')
@section('heading', 'Confirm your password')
@section('lead', 'Please confirm your password before continuing.')

@section('content')
    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
        @csrf

        <div>
            <x-ui.label size="lg" for="password">Password</x-ui.label>
            @include('auth.partials.password-input', ['name' => 'password', 'autocomplete' => 'current-password', 'autofocus' => true])
            @if (Route::has('password.request'))
                <div class="mt-2 text-right">
                    <a href="{{ route('password.request') }}" class="text-[15px] text-primary no-underline hover:underline">Forgot your password?</a>
                </div>
            @endif
        </div>

        <x-ui.button type="submit" size="lg" class="w-full">Confirm password</x-ui.button>
    </form>

    @include('auth.partials.password-toggle')
@endsection
