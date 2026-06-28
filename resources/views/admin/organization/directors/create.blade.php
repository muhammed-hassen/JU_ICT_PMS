@extends('layouts.master')

@section('subtitle', 'Create Director')
@section('content_header_title', 'Create Director')
@section('content_header_subtitle', 'ICT leadership')

@section('content_body')
    <form action="{{ route('admin.organization.directors.store') }}" method="POST">
        @csrf
        @include('admin.organization.directors._form', ['submitLabel' => 'Create Director'])
    </form>
@stop
