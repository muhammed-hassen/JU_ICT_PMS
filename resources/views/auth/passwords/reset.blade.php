@extends('layouts.auth')

@section('title', 'Choose a new password')
@section('heading', 'Choose a new password')

@section('content')
    <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <x-ui.label size="lg" for="email">Email address</x-ui.label>
            <x-ui.input size="lg" id="email" type="email" name="email" :value="$email ?? old('email')" :invalid="$errors->has('email')"
                        autocomplete="email" required />
            @error('email')
                <p class="m-0 mt-1.5 text-[13px] text-destructive" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <x-ui.label size="lg" for="password">New password</x-ui.label>
            @include('auth.partials.password-input', ['name' => 'password', 'autocomplete' => 'new-password', 'autofocus' => true])
        </div>

        <div>
            <x-ui.label size="lg" for="password-confirm">Confirm new password</x-ui.label>
            <x-ui.input size="lg" id="password-confirm" type="password" name="password_confirmation" autocomplete="new-password" required />
        </div>

        <x-ui.button type="submit" size="lg" class="w-full">Reset password</x-ui.button>
    </form>

    @include('auth.partials.password-toggle')
@endsection
