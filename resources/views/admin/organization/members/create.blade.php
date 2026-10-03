@extends('layouts.console')

@section('title', 'Add Member to Team')

@section('content_header')
    <x-ui.page-header title="Add Member to Team" description="Pick an existing user and assign them to one or more teams." />
@stop

@section('content')
    <form action="{{ route('admin.organization.members.store') }}" method="POST">
        @csrf
        @include('admin.organization.members._form', ['submitLabel' => 'Add to Team'])
    </form>
@stop
