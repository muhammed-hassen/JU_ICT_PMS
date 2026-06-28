@extends('layouts.master')

@section('subtitle', 'Create Member')
@section('content_header_title', 'Create Member')
@section('content_header_subtitle', 'Team membership')

@section('content_body')
    <form action="{{ route('admin.organization.members.store') }}" method="POST">
        @csrf
        @include('admin.organization.members._form', ['submitLabel' => 'Create Member'])
    </form>
@stop
