@extends('layouts.master')

@section('subtitle', 'Create Team Leader')
@section('content_header_title', 'Create Team Leader')
@section('content_header_subtitle', 'Team leadership')

@section('content_body')
    <form action="{{ route('admin.organization.team-leaders.store') }}" method="POST">
        @csrf
        @include('admin.organization.team-leaders._form', ['submitLabel' => 'Create Team Leader'])
    </form>
@stop
