{{--
    Signed-out pages: sign in, password reset, confirm, verify.
    Modelled on the JU Git sign-in page: crest, title, one column, footer links.
--}}
@php
    $brandName = config('navigation.brand');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sign in') · {{ $brandName }}</title>
    <link rel="icon" href="{{ asset('images/ju-logo.jpg') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700&family=Source+Sans+3:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="{{ asset('css/ju.css') }}?v={{ @filemtime(public_path('css/ju.css')) }}">
</head>
<body class="flex min-h-screen flex-col bg-card text-[15px]">

    <main class="flex flex-1 flex-col items-center px-4 pb-16 pt-14 sm:pt-20">
        <div class="w-full max-w-[560px]">
            <a href="{{ url('/') }}" class="mx-auto block w-fit no-underline">
                <img src="{{ asset('images/ju-logo.jpg') }}" alt="Jimma University crest" class="h-[72px] w-auto">
            </a>

            <h1 class="mb-1 mt-6 text-center font-display text-2xl font-semibold tracking-tight text-foreground">
                @yield('heading', 'JU ICT Project Management')
            </h1>
            @hasSection('lead')
                <p class="m-0 text-center text-sm text-muted-foreground">@yield('lead')</p>
            @endif

            <div class="mt-8">
                @if (session('status'))
                    <div class="mb-5 rounded-lg border border-success/25 bg-success/5 px-4 py-3 text-sm text-success" role="status">
                        {{ session('status') }}
                    </div>
                @endif

                @if (session('resent'))
                    <div class="mb-5 rounded-lg border border-success/25 bg-success/5 px-4 py-3 text-sm text-success" role="status">
                        A fresh verification link has been sent to your email address.
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </main>

    <footer class="border-t border-border">
        <div class="mx-auto flex w-full max-w-5xl flex-wrap items-center justify-between gap-x-6 gap-y-2 px-6 py-5 text-sm">
            <nav class="flex flex-wrap items-center gap-x-6 gap-y-2">
                <a href="https://ju.edu.et" target="_blank" rel="noopener noreferrer" class="text-primary no-underline hover:underline">Jimma University</a>
            </nav>
            <span class="text-muted-foreground">&copy; {{ date('Y') }} JU-ICT Team</span>
        </div>
    </footer>

    @stack('js')
</body>
</html>
