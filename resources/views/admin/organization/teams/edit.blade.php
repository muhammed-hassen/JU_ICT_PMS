@extends('layouts.console')

@section('title', 'Edit Team')

@section('content_header')
    <x-ui.page-header title="Edit Team" :description="$team->name" />
@stop

@section('content')
    <form action="{{ route('admin.organization.teams.update', $team) }}" method="POST">
        @csrf
        @method('PUT')
        @include('admin.organization.teams._form', ['submitLabel' => 'Update Team'])
    </form>
@stop
