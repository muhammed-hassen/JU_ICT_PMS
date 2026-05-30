@extends('layouts.master')

@section('subtitle', 'Edit Role')
@section('content_header_title', 'Edit Role')
@section('content_header_subtitle', $role->name)

@section('content_body')
    <div class="card">
        <form action="{{ route('admin.roles.update', $role) }}" method="POST">
            @csrf
            @method('PUT')
            @include('admin.rbac.roles._form', ['submitLabel' => 'Update Role'])
        </form>
    </div>
@stop
