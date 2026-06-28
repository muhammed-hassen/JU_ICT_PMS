
@php
    $brandName = config('app.name') !== 'Laravel' ? config('app.name') : 'JU ICT PMS';
    $logoPath  = file_exists(public_path('images/ju-logo.jpg'))
        ? asset('images/ju-logo.jpg')
        : (config('adminlte.auth_logo.enabled', false)
            ? asset(config('adminlte.auth_logo.img.path'))
            : asset(config('adminlte.logo_img')));
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign In — {{ $brandName }}</title>

    {{-- FontAwesome (already bundled with AdminLTE) --}}
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:wght@400;700&family=Source+Sans+3:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- JU Brand Theme --}}
    <link rel="stylesheet" href="{{ asset('css/ju-custom.css') }}">
</head>
<body class="ju-auth-shell">

    {{-- ═══════════════════════════════════════════════
         TOP BAR
    ════════════════════════════════════════════════ --}}
    <header class="ju-auth-topbar">
        <a class="ju-auth-topbar-brand" href="{{ url('/') }}">
            <img src="{{ $logoPath }}" alt="Jimma University Logo">
            <span><em>JU</em> ICT PMS</span>
        </a>
        <span class="ju-auth-topbar-tagline">Jimma University — ICT Development Office</span>
    </header>

    {{-- ═══════════════════════════════════════════════
         MAIN
    ════════════════════════════════════════════════ --}}
    <main class="ju-auth-main">
        <div class="ju-auth-container">

            {{-- Flash: status --}}
            @if (session('status'))
                <div class="ju-auth-banner ju-banner-success" role="status">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Flash: validation errors --}}
            @if ($errors->any())
                <div class="ju-auth-banner" role="alert" aria-live="assertive">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- ── Branding ── --}}
            <div class="ju-auth-branding">
                <div class="ju-auth-logo-halo">
                    <img src="{{ $logoPath }}" alt="{{ $brandName }} Logo">
                </div>
                <h1><span>Jimma</span> University</h1>
                <p>ICT Project Management System</p>
            </div>

            {{-- ── Card ── --}}
            <div class="ju-auth-card">

                <div class="ju-auth-card-heading">
                    <i class="fas fa-sign-in-alt"></i>
                    Sign in to your account
                </div>

                <form class="ju-auth-form" method="POST" action="{{ route('login') }}">
                    @csrf

                    {{-- Email --}}
                    <div class="ju-auth-field">
                        <label for="email">Email Address</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            class="ju-auth-input @error('email') is-invalid @enderror"
                            autocomplete="username"
                            placeholder="your@ju.edu.et"
                            autofocus
                            required
                        >
                        @error('email')
                            <p class="ju-auth-error" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="ju-auth-field">
                        <label for="password">Password</label>
                        <div class="ju-pw-wrap">
                            <input
                                id="password"
                                type="password"
                                name="password"
                                class="ju-auth-input @error('password') is-invalid @enderror"
                                autocomplete="current-password"
                                placeholder="••••••••••"
                                required
                            >
                            <button
                                type="button"
                                class="ju-pw-toggle"
                                aria-label="Show password"
                                data-ju-pw-toggle
                            >
                                <i class="fas fa-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                        @error('password')
                            <p class="ju-auth-error" role="alert">{{ $message }}</p>
                        @enderror

                        {{-- Forgot password link --}}
                        @if (Route::has('password.request'))
                            <div class="ju-auth-meta">
                                <a href="{{ route('password.request') }}">Forgot your password?</a>
                            </div>
                        @endif
                    </div>

                    {{-- Remember me --}}
                    <label class="ju-auth-remember" for="remember">
                        <input
                            id="remember"
                            type="checkbox"
                            name="remember"
                            {{ old('remember') ? 'checked' : '' }}
                        >
                        <span>Remember me</span>
                    </label>

                    {{-- Submit --}}
                    <button type="submit" class="ju-auth-submit">
                        <i class="fas fa-sign-in-alt" aria-hidden="true"></i>
                        <span>Sign In</span>
                    </button>

                </form>

                {{-- Register link --}}
                @if (Route::has('register'))
                    <div class="ju-auth-card-footer">
                        Don't have an account?
                        <a href="{{ route('register') }}">Register here</a>
                    </div>
                @endif

            </div>{{-- /.ju-auth-card --}}

        </div>{{-- /.ju-auth-container --}}
    </main>

    {{-- ═══════════════════════════════════════════════
         BOTTOM BAR
    ════════════════════════════════════════════════ --}}
    <footer class="ju-auth-bottom-bar">
        &copy; {{ date('Y') }}
        <a href="https://ju.edu.et" target="_blank" rel="noopener noreferrer">Jimma University</a>
        &mdash; ICT Development Office. All rights reserved.
    </footer>

    {{-- Password toggle script — no extra libraries --}}
    <script>
        document.querySelectorAll('[data-ju-pw-toggle]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var field = document.getElementById('password');
                var icon  = btn.querySelector('i');
                var reveal = field.type === 'password';

                field.type = reveal ? 'text' : 'password';
                btn.setAttribute('aria-label', reveal ? 'Hide password' : 'Show password');
                icon.classList.toggle('fa-eye',       !reveal);
                icon.classList.toggle('fa-eye-slash',  reveal);
            });
        });
    </script>

</body>
</html>
