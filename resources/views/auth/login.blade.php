@extends('layouts.auth')

@section('title', 'Sign in')

@section('content')
    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <x-ui.label size="lg" for="email">Email address</x-ui.label>
            <x-ui.input size="lg" id="email" type="email" name="email" :value="old('email')" :invalid="$errors->has('email')"
                        autocomplete="username" autofocus required />
            @error('email')
                <p class="m-0 mt-1.5 text-[13px] text-destructive" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <x-ui.label size="lg" for="password">Password</x-ui.label>
            @include('auth.partials.password-input', ['name' => 'password', 'autocomplete' => 'current-password'])
            @if (Route::has('password.request'))
                <div class="mt-2 text-right">
                    <a href="{{ route('password.request') }}" class="text-[15px] text-primary no-underline hover:underline">Forgot your password?</a>
                </div>
            @endif
        </div>

        <label for="remember" class="mb-0 flex w-fit cursor-pointer items-center gap-2.5 text-[15px] font-normal text-foreground">
            <input id="remember" type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}
                   class="size-4 accent-primary">
            Remember me
        </label>

        <x-ui.button type="submit" size="lg" class="w-full">Sign in</x-ui.button>
    </form>

    @if (Route::has('register'))
        <p class="m-0 mt-6 text-center text-sm text-muted-foreground">
            Don't have an account?
            <a href="{{ route('register') }}" class="text-primary no-underline hover:underline">Register</a>
        </p>
    @endif

    @include('auth.partials.password-toggle')
@endsection
