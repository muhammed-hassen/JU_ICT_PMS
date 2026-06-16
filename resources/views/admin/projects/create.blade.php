@extends('layouts.master')

@section('subtitle', 'Create Project')
@section('content_header_title', 'Create Project')
@section('content_header_subtitle', 'Add a new project')

@section('content_body')
    <div class="card">
        <form action="{{ route('admin.projects.store') }}" method="POST">
            @csrf
            @include('admin.projects._form', ['submitLabel' => 'Create Project'])
        </form>
    </div>
@stop