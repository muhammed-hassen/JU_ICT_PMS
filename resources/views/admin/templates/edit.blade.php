@extends('layouts.master')

@section('subtitle', 'Edit Template')
@section('content_header_title', 'Edit Template')
@section('content_header_subtitle', 'Update the default phase and task structure')

@section('content_body')
    <div class="card card-elevated">
        <form action="{{ route('admin.templates.update', $template) }}" method="POST">
            @csrf
            @method('PUT')
            @include('admin.templates._form', ['submitLabel' => $submitLabel])
        </form>
    </div>
@stop
