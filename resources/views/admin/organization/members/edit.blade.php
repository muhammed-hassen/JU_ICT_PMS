@extends('layouts.master')

@section('subtitle', 'Edit Member')
@section('content_header_title', 'Edit Member')
@section('content_header_subtitle', $member->name)

@section('content_body')
    <form action="{{ route('admin.organization.members.update', $member) }}" method="POST">
        @csrf
        @method('PUT')
        @include('admin.organization.members._form', ['submitLabel' => 'Update Member'])
    </form>
@stop
