@extends('layouts.master')

@section('subtitle', 'Edit Team Leader')
@section('content_header_title', 'Edit Team Leader')
@section('content_header_subtitle', $teamLeader->name)

@section('content_body')
    <form action="{{ route('admin.organization.team-leaders.update', $teamLeader) }}" method="POST">
        @csrf
        @method('PUT')
        @include('admin.organization.team-leaders._form', ['submitLabel' => 'Update Team Leader'])
    </form>
@stop
