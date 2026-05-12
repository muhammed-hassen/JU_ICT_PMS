@extends('layouts.master')
{{-- Customize layout sections --}}
@section('subtitle', 'Posts')
@section('content_header_title', 'Posts')
@section('content_header_subtitle', 'Manage your blog posts')
{{-- Content body: main page content --}}
@section('content_body')
    <p>Here you can view, create, and manage all your blog posts.</p>
@stop
{{-- Push extra CSS --}}
@push('css')
    {{-- Add here extra stylesheets --}}
@endpush
{{-- Push extra scripts --}}
@push('js')
    <script> console.log("Posts page loaded successfully!"); </script>
@endpush