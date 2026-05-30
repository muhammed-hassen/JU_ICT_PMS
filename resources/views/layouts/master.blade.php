@extends('adminlte::page')

@push('css')
<style>
    /* Fix scrolling issues */
    .wrapper {
        min-height: 100vh;
        overflow-x: hidden;
    }
    
    .main-sidebar {
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        overflow-y: auto;
        z-index: 1000;
    }
    
    .content-wrapper {
        margin-left: 250px;
        min-height: 100vh;
        overflow-x: auto;
    }
    
    @media (max-width: 767px) {
        .content-wrapper {
            margin-left: 0;
        }
    }
    
    /* Card height control */
    .card {
        margin-bottom: 1.5rem;
    }
    
    .card-body {
        max-height: calc(100vh - 250px);
        overflow-y: auto;
    }
    
    /* Table container */
    .table-responsive {
        overflow-x: auto;
        overflow-y: visible;
    }
    
    /* Fix pagination area */
    .card-footer {
        position: sticky;
        bottom: 0;
        background: white;
        border-top: 1px solid rgba(0, 0, 0, 0.125);
    }
</style>
@endpush

{{-- ════════════════════════════════════════════════════════
     BROWSER TITLE
════════════════════════════════════════════════════════ --}}
@section('title')
    {{ config('adminlte.title', 'JU ICT PMS') }}@hasSection('subtitle') — @yield('subtitle')@endif
@stop

{{-- ════════════════════════════════════════════════════════
     EXTRA CSS  (loaded in <head>)
════════════════════════════════════════════════════════ --}}
@push('css')
    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:wght@400;700&family=Source+Sans+3:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- JU Brand Theme --}}
    <link rel="stylesheet" href="{{ asset('css/ju-custom.css') }}">
@endpush

{{-- ════════════════════════════════════════════════════════
     PAGE CONTENT HEADER  (breadcrumb bar)
════════════════════════════════════════════════════════ --}}
@section('content_header')
    @hasSection('content_header_title')
        <h1 class="text-muted">
            @yield('content_header_title')
            @hasSection('content_header_subtitle')
                <small class="text-dark">
                    <i class="fas fa-xs fa-angle-right text-muted"></i>
                    @yield('content_header_subtitle')
                </small>
            @endif
        </h1>
    @endif
@stop

{{-- ════════════════════════════════════════════════════════
     CONTENT  — child views use @section('content_body')
════════════════════════════════════════════════════════ --}}
@section('content')
    @yield('content_body')
@stop

{{-- ════════════════════════════════════════════════════════
     FOOTER
════════════════════════════════════════════════════════ --}}
@section('footer')
    <div class="float-right">
        Version: {{ config('app.version', '1.0.0') }}
    </div>
    <strong>
        <a href="{{ config('app.company_url', 'https://ju.edu.et') }}" target="_blank" rel="noopener">
            {{ config('app.company_name', 'Jimma University') }}
        </a>
    </strong>
    &mdash; ICT Development Office
@stop

{{-- ════════════════════════════════════════════════════════
     EXTRA JS  (loaded before </body>)
════════════════════════════════════════════════════════ --}}
@push('js')
<script>
    $(document).ready(function () {
        // Shared page-level scripts go here
    });
</script>
@endpush