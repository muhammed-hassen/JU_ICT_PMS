@extends('layouts.master')

@section('subtitle', 'Edit Director')
@section('content_header_title', 'Edit Director')
@section('content_header_subtitle', $director->name)

@section('content_body')
    <form action="{{ route('admin.organization.directors.update', $director) }}" method="POST">
        @csrf
        @method('PUT')
        @include('admin.organization.directors._form', ['submitLabel' => 'Update Director'])
    </form>
@stop
