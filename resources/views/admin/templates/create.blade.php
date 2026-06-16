@extends('layouts.master')

@section('subtitle', 'New Template')
@section('content_header_title', 'Create Template')
@section('content_header_subtitle', 'Build the default phase and task structure')

@section('content_body')
    <div class="card card-elevated">
        <form action="{{ route('admin.templates.store') }}" method="POST">
            @csrf
            @include('admin.templates._form', ['submitLabel' => $submitLabel])
        </form>
    </div>
@stop
