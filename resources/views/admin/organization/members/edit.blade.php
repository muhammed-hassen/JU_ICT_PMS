@extends('layouts.console')

@section('title', 'Edit Member')

@section('content_header')
    <x-ui.page-header title="Edit Member" :description="$member->name" />
@stop

@section('content')
    <form action="{{ route('admin.organization.members.update', $member) }}" method="POST">
        @csrf
        @method('PUT')
        @include('admin.organization.members._form', ['submitLabel' => 'Update Member'])
    </form>
@stop
