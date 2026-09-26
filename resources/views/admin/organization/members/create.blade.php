@extends('layouts.console')

@section('title', 'Create Member')

@section('content_header')
    <x-ui.page-header title="Create Member" description="Add a person and choose their teams." />
@stop

@section('content')
    <form action="{{ route('admin.organization.members.store') }}" method="POST">
        @csrf
        @include('admin.organization.members._form', ['submitLabel' => 'Create Member'])
    </form>
@stop
