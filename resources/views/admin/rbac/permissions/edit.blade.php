@extends('layouts.master')

@section('subtitle', 'Edit Permission')
@section('content_header_title', 'Edit Permission')
@section('content_header_subtitle', $permission->name)

@section('content_body')
    <div class="card">
        <form action="{{ route('admin.permissions.update', $permission) }}" method="POST">
            @csrf
            @method('PUT')
            @include('admin.rbac.permissions._form', ['submitLabel' => 'Update Permission'])
        </form>
    </div>
@stop
