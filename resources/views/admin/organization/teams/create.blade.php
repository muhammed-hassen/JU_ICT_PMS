@extends('layouts.console')

@section('title', 'Create Team')

@section('content_header')
    <x-ui.page-header title="Create Team" description="Set up a team, its leader and its members." />
@stop

@section('content')
    <form action="{{ route('admin.organization.teams.store') }}" method="POST">
        @csrf
        @include('admin.organization.teams._form', ['submitLabel' => 'Create Team'])
    </form>
@stop
