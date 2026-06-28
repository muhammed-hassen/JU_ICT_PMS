@extends('layouts.master')

@section('subtitle', 'Edit Team')
@section('content_header_title', 'Edit Team')
@section('content_header_subtitle', $team->name)

@section('content_body')
    <form action="{{ route('admin.organization.teams.update', $team) }}" method="POST">
        @csrf
        @method('PUT')
        @include('admin.organization.teams._form', ['submitLabel' => 'Update Team'])
    </form>
@stop
