{{--
    The signed-in app shell: sidebar, topbar and page content.

    Views fill these sections: title, content_header, content, footer, and
    css / js as either a @section or a @push.
--}}
@php
    $brandName = config('navigation.brand');
    // Inline @section values arrive already escaped; decode so the <title> below escapes once, not twice.
    $pageTitle = html_entity_decode(trim($__env->yieldContent('subtitle')) ?: trim($__env->yieldContent('title')), ENT_QUOTES);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle && $pageTitle !== $brandName ? $pageTitle . ' · ' . $brandName : $brandName }}</title>
    <link rel="icon" href="{{ asset('images/ju-logo.jpg') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700&family=Source+Sans+3:wght@400;500;600;700&display=swap">

    <link rel="stylesheet" href="{{ asset('css/ju.css') }}?v={{ @filemtime(public_path('css/ju.css')) }}">

    @stack('css')
    @yield('css')
</head>
<body class="min-h-screen bg-background">

    @include('layouts.partials.sidebar')

    {{-- Dims the page behind the sidebar on small screens. --}}
    <div data-shell-backdrop class="fixed inset-0 z-30 hidden bg-ju-navy/30 lg:hidden" aria-hidden="true"></div>

    <div class="flex min-h-screen flex-col lg:pl-[264px]">
        @include('layouts.partials.topbar')

        <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
            <div class="mx-auto w-full max-w-[1440px]">
                @hasSection('content_header')
                    <div class="content-header mb-6 p-0">
                        @yield('content_header')
                    </div>
                @endif

                {{-- Success and error messages for every page, so a redirect never looks like a silent refresh. --}}
                <x-ui.flash />
                @yield('content')
            </div>
        </main>

        <footer class="border-t border-border px-4 py-4 text-[13px] text-muted-foreground sm:px-6 lg:px-8">
            <div class="mx-auto flex w-full max-w-[1440px] flex-wrap items-center justify-between gap-2">
                @hasSection('footer')
                    @yield('footer')
                @else
                    <span>&copy; {{ date('Y') }} <a href="https://ju.edu.et" target="_blank" rel="noopener noreferrer" class="font-medium text-foreground hover:text-primary">Jimma University</a>, JU-ICT Team</span>
                    <span class="tabular">v{{ config('app.version', '1.0.0') }}</span>
                @endif
            </div>
        </footer>
    </div>

    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/lucide/lucide.min.js') }}"></script>
    <script>
        (function () {
            var body = document.body;
            var backdrop = document.querySelector('[data-shell-backdrop]');

            function setSidebar(open) {
                body.toggleAttribute('data-sidebar-open', open);
                backdrop.classList.toggle('hidden', !open);
            }

            document.querySelectorAll('[data-sidebar-toggle]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    setSidebar(!body.hasAttribute('data-sidebar-open'));
                });
            });
            backdrop.addEventListener('click', function () { setSidebar(false); });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') setSidebar(false);
            });

            // Close the user menu when clicking anywhere else.
            document.addEventListener('click', function (e) {
                document.querySelectorAll('details[data-menu][open]').forEach(function (d) {
                    if (!d.contains(e.target)) d.removeAttribute('open');
                });
            });

            if (window.lucide) lucide.createIcons();
        })();
    </script>

    @stack('js')
    @yield('js')
</body>
</html>
