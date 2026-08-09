@extends('layouts.master')

@section('subtitle', 'Edit Project')
@section('content_header_title', 'Edit Project')
@section('content_header_subtitle', $project->name)

@section('content_body')
    <div class="card">
        <form action="{{ route('admin.projects.update', $project) }}" method="POST">
            @csrf
            @method('PUT')
            @include('admin.projects._form', ['submitLabel' => 'Update Project'])
        </form>
    </div>
@stop