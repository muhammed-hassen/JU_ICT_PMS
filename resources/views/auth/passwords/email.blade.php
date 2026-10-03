@extends('layouts.auth')

@section('title', 'Reset password')
@section('heading', 'Reset your password')
@section('lead', 'Enter your email and we will send you a reset link.')

@section('content')
    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <div>
            <x-ui.label size="lg" for="email">Email address</x-ui.label>
            <x-ui.input size="lg" id="email" type="email" name="email" :value="old('email')" :invalid="$errors->has('email')"
                        autocomplete="email" autofocus required />
            @error('email')
                <p class="m-0 mt-1.5 text-[13px] text-destructive" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <x-ui.button type="submit" size="lg" class="w-full">Send reset link</x-ui.button>
    </form>

    <p class="m-0 mt-6 text-center text-sm">
        <a href="{{ route('login') }}" class="text-primary no-underline hover:underline">Back to sign in</a>
    </p>
@endsection
