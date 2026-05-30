@extends('layouts.master')

@section('subtitle', 'Create Role')
@section('content_header_title', 'Create Role')
@section('content_header_subtitle', 'Define a role and assign permissions')

@section('content_body')
    <div class="card">
        <form action="{{ route('admin.roles.store') }}" method="POST">
            @csrf
            @include('admin.rbac.roles._form', ['submitLabel' => 'Create Role'])
        </form>
    </div>
@stop
