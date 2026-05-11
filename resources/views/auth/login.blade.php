@php
    $brandName = config('app.name') !== 'Laravel' ? config('app.name') : 'JU ICT PMS';
    $logoPath = file_exists(public_path('images/ju-logo.jpg'))
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
    <title>{{ $brandName }}</title>
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <style>
        :root {
            color-scheme: light;
            --default-regular-font: "GitLab Sans";
            --default-mono-font: "GitLab Mono";
            --font-family-sans-serif: var(--default-regular-font), -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Noto Sans", Ubuntu, Cantarell, "Helvetica Neue", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
            --font-family-monospace: var(--default-mono-font), "JetBrains Mono", "Menlo", "DejaVu Sans Mono", "Liberation Mono", "Consolas", "Ubuntu Mono", "Courier New", "andale mono", "lucida console", monospace;
            --gl-text-primary: #28272d;
            --gl-text-secondary: #737278;
            --gl-text-tertiary: #89888d;
            --gl-line-height-24: 1.5rem;
            --gl-line-height-20: 1.25rem;
            --gl-line-height-16: 1rem;
            --gl-color-neutral-900: #28272d;
            --gl-color-neutral-800: #3a383f;
            --gl-color-neutral-600: #626168;
            --gl-color-neutral-500: #737278;
            --gl-color-neutral-400: #89888d;
            --gl-color-neutral-200: #bfbfc3;
            --gl-color-neutral-100: #dcdcde;
            --gl-color-neutral-50: #ececef;
            --gl-color-neutral-10: #fbfafd;
            --gl-color-neutral-0: #fff;
            --gl-color-blue-700: #0b5cad;
            --gl-color-blue-500: #1f75cb;
            --gl-color-blue-100: #cbe2f9;
            --gl-color-red-600: #c91c00;
            --gl-color-red-500: #dd2b0e;
            --auth-bg: #fbfafd;
            --auth-surface: #ffffff;
            --auth-border: var(--gl-color-neutral-100);
            --auth-border-strong: var(--gl-color-neutral-200);
            --auth-text: var(--gl-text-primary);
            --auth-muted: var(--gl-text-secondary);
            --auth-link: var(--gl-color-blue-700);
            --auth-link-hover: var(--gl-color-blue-500);
            --auth-button: var(--gl-color-blue-500);
            --auth-button-hover: #1068bf;
            --auth-button-text: #ffffff;
            --auth-danger: var(--gl-color-red-500);
            --auth-shadow: 0 1px 2px rgba(5, 5, 6, 0.06), 0 12px 40px rgba(5, 5, 6, 0.06);
            --auth-radius: 12px;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            min-height: 100%;
            margin: 0;
            -webkit-text-size-adjust: 100%;
            -webkit-tap-highlight-color: rgba(5, 5, 6, 0);
            background: linear-gradient(180deg, #ffffff 0%, #f7f6fb 52%, #eef3fc 100%);
            color: var(--auth-text);
            font-family: var(--font-family-sans-serif);
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            font-variant-ligatures: none;
        }

        a {
            color: var(--auth-link);
            text-decoration: none;
        }

        a:hover {
            color: var(--auth-link-hover);
            text-decoration: underline;
        }

        .auth-shell {
            display: flex;
            min-height: 100vh;
            flex-wrap: wrap;
            background:
                radial-gradient(circle at top left, rgba(169, 137, 245, 0.11), transparent 30%),
                radial-gradient(circle at bottom right, rgba(31, 117, 203, 0.12), transparent 32%);
        }

        .auth-main {
            width: 100%;
            align-self: center;
            padding: 2.5rem 1.25rem 2rem;
        }

        .auth-container,
        .auth-footer-inner {
            width: 100%;
            max-width: 1120px;
            margin: 0 auto;
        }

        .auth-content {
            width: 100%;
        }

        .auth-flash {
            position: sticky;
            top: 1rem;
            z-index: 10;
        }

        .auth-stack {
            margin: 2.5rem 0;
        }

        .auth-branding {
            text-align: center;
            margin-bottom: 2rem;
        }

        .auth-branding img {
            width: 4.25rem;
            height: 4.25rem;
            object-fit: contain;
            display: inline-block;
            margin-bottom: 1rem;
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(31, 117, 203, 0.12);
            background: rgba(255, 255, 255, 0.88);
            padding: 0.45rem;
        }

        .auth-branding h1 {
            margin: 0;
            font-size: clamp(1.75rem, 2vw + 1rem, 2.25rem);
            line-height: 1.15;
            letter-spacing: -0.03em;
            font-weight: 700;
        }

        .auth-branding p {
            margin: 0.65rem 0 0;
            color: var(--auth-muted);
            font-size: 1rem;
        }

        .auth-form-wrap {
            width: min(100%, 31rem);
            margin: 0 auto;
        }

        .auth-card {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(220, 220, 222, 0.95);
            border-radius: var(--auth-radius);
            box-shadow: var(--auth-shadow);
            padding: 1.5rem;
            backdrop-filter: blur(10px);
        }

        .auth-form {
            display: flex;
            flex-direction: column;
            gap: 1.15rem;
        }

        .auth-field {
            display: flex;
            flex-direction: column;
            gap: 0.45rem;
        }

        .auth-field label {
            font-size: 1rem;
            font-weight: 600;
            color: var(--auth-text);
        }

        .auth-input {
            width: 100%;
            min-height: 2.8rem;
            border: 1px solid var(--auth-border);
            border-radius: 0.6rem;
            background: #ffffff;
            color: var(--auth-text);
            padding: 0.72rem 0.9rem;
            font-size: 1rem;
            outline: none;
            transition: border-color 0.18s ease, box-shadow 0.18s ease;
        }

        .auth-input:focus {
            border-color: var(--auth-link);
            box-shadow: 0 0 0 3px rgba(31, 117, 203, 0.12);
        }

        .auth-input.is-invalid {
            border-color: var(--auth-danger);
            box-shadow: 0 0 0 3px rgba(221, 43, 14, 0.08);
        }

        .auth-password-wrap {
            position: relative;
        }

        .auth-password-wrap .auth-input {
            padding-right: 3rem;
        }

        .auth-toggle {
            position: absolute;
            top: 50%;
            right: 0.45rem;
            transform: translateY(-50%);
            border: 0;
            background: transparent;
            color: var(--auth-muted);
            width: 2.2rem;
            height: 2.2rem;
            border-radius: 999px;
            cursor: pointer;
            transition: background-color 0.18s ease, color 0.18s ease;
        }

        .auth-toggle:hover {
            background: rgba(31, 117, 203, 0.08);
            color: var(--auth-link);
        }

        .auth-meta {
            display: flex;
            justify-content: flex-end;
            font-size: 0.95rem;
        }

        .auth-remember {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            color: var(--auth-text);
            font-size: 1rem;
        }

        .auth-remember input {
            width: 1rem;
            height: 1rem;
            accent-color: var(--auth-button);
        }

        .auth-submit {
            display: inline-flex;
            width: 100%;
            justify-content: center;
            align-items: center;
            gap: 0.6rem;
            min-height: 2.95rem;
            border: 0;
            border-radius: 0.7rem;
            background: linear-gradient(135deg, var(--auth-button) 0%, #3b5bd6 100%);
            color: var(--auth-button-text);
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 10px 22px rgba(31, 117, 203, 0.2);
            transition: transform 0.18s ease, box-shadow 0.18s ease, filter 0.18s ease;
        }

        .auth-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 28px rgba(31, 117, 203, 0.24);
            filter: brightness(1.02);
        }

        .auth-submit:focus-visible {
            outline: 3px solid rgba(31, 117, 203, 0.22);
            outline-offset: 2px;
        }

        .auth-error,
        .auth-status {
            margin: 0;
            font-size: 0.95rem;
            line-height: 1.45;
        }

        .auth-error {
            color: var(--auth-danger);
        }

        .auth-banner {
            margin-bottom: 1rem;
            padding: 0.95rem 1rem;
            border-radius: 0.85rem;
            border: 1px solid rgba(221, 43, 14, 0.15);
            background: rgba(252, 241, 239, 0.9);
            color: #8d1300;
            box-shadow: 0 12px 26px rgba(5, 5, 6, 0.03);
        }

        .auth-banner.auth-banner-status {
            border-color: rgba(16, 133, 72, 0.18);
            background: rgba(236, 244, 238, 0.94);
            color: #0d532a;
        }

        .auth-banner ul {
            margin: 0;
            padding-left: 1.1rem;
        }

        @media (max-width: 767px) {
            .auth-main {
                padding: 1.5rem 1rem 1.25rem;
            }

            .auth-stack {
                margin: 1.5rem 0;
            }

            .auth-card {
                padding: 1.15rem;
            }
        }
    </style>
</head>
<body>
    <div class="auth-shell">
        <main class="auth-main">
            <div class="auth-container">
                <div class="auth-content">
                    <div class="auth-flash">
                        @if (session('status'))
                            <div class="auth-banner auth-banner-status">
                                {{ session('status') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="auth-banner" aria-live="assertive">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                    <div class="auth-stack">
                        <div class="auth-branding">
                            <img src="{{ $logoPath }}" alt="{{ $brandName }} logo">
                            <h1>{{ $brandName }}</h1>
                            <p>Sign in to access the internal project management workspace.</p>
                        </div>

                        <div class="auth-form-wrap">
                            <div class="auth-card">
                                <form class="auth-form" method="POST" action="{{ route('login') }}">
                                    @csrf

                                    <div class="auth-field">
                                        <label for="email">Username or email</label>
                                        <input
                                            id="email"
                                            type="email"
                                            name="email"
                                            value="{{ old('email') }}"
                                            class="auth-input @error('email') is-invalid @enderror"
                                            autocomplete="username"
                                            autofocus
                                            required
                                        >
                                        @error('email')
                                            <p class="auth-error">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="auth-field">
                                        <label for="password">Password</label>
                                        <div class="auth-password-wrap">
                                            <input
                                                id="password"
                                                type="password"
                                                name="password"
                                                class="auth-input @error('password') is-invalid @enderror"
                                                autocomplete="current-password"
                                                required
                                            >
                                            <button type="button" class="auth-toggle" aria-label="Show password" data-password-toggle>
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                        @error('password')
                                            <p class="auth-error">{{ $message }}</p>
                                        @enderror
                                        <div class="auth-meta">
                                            @if (Route::has('password.request'))
                                                <a href="{{ route('password.request') }}">Forgot your password?</a>
                                            @endif
                                        </div>
                                    </div>

                                    <label class="auth-remember" for="remember">
                                        <input id="remember" type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                                        <span>Remember me</span>
                                    </label>

                                    <button type="submit" class="auth-submit">
                                        <span>Sign in</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

    </div>

    <script>
        document.querySelectorAll('[data-password-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                const field = document.getElementById('password');
                const icon = button.querySelector('i');
                const showPassword = field.type === 'password';

                field.type = showPassword ? 'text' : 'password';
                button.setAttribute('aria-label', showPassword ? 'Hide password' : 'Show password');
                icon.classList.toggle('fa-eye', !showPassword);
                icon.classList.toggle('fa-eye-slash', showPassword);
            });
        });
    </script>
</body>
</html>
