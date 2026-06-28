@extends('layouts.master')

@section('subtitle', 'Create Team')
@section('content_header_title', 'Create Team')
@section('content_header_subtitle', 'Organization structure')

@section('content_body')
    <form action="{{ route('admin.organization.teams.store') }}" method="POST">
        @csrf
        @include('admin.organization.teams._form', ['submitLabel' => 'Create Team'])
    </form>
@stop
