@extends('layouts.master')

@section('subtitle', 'Create Permission')
@section('content_header_title', 'Create Permission')
@section('content_header_subtitle', 'Add a permission to the catalog')

@section('content_body')
    <div class="card">
        <form action="{{ route('admin.permissions.store') }}" method="POST">
            @csrf
            @include('admin.rbac.permissions._form', ['submitLabel' => 'Create Permission'])
        </form>
    </div>
@stop
