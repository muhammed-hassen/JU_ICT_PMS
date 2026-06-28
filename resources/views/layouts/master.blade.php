@extends('adminlte::page')

@push('css')
<style>
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
</style>

<link rel="stylesheet" href="{{ asset('css/ju-custom.css') }}">
@endpush

@section('title')
    {{ config('adminlte.title', 'JU ICT PMS') }}
@endsection

@section('content_header')
    <h1>
        @yield('content_header_title')

        @hasSection('content_header_subtitle')
            <small>
                @yield('content_header_subtitle')
            </small>
        @endif
    </h1>
@endsection

@section('content')
    @yield('content_body')
@endsection

@section('footer')
    <div class="float-right">
        Version: {{ config('app.version', '1.0.0') }}
    </div>

    <strong>
        <a href="https://ju.edu.et" target="_blank">
            Jimma University
        </a>
    </strong>
    — ICT Development Office
@endsection

@push('js')
<script>
    $(function () {
        // Shared scripts
    });
</script>
@endpush
