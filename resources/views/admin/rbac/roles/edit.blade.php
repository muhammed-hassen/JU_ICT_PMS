@extends('layouts.console')

@section('title', 'Edit Role')

@section('content_header')
    @canvisit(route('admin.roles.index'))
        <a href="{{ route('admin.roles.index') }}" class="mb-3 inline-flex items-center gap-1.5 text-[13px] font-medium text-muted-foreground no-underline hover:text-foreground hover:no-underline">
            <i data-lucide="arrow-left" class="size-4"></i>
            Roles
        </a>
    @endcanvisit
    <x-ui.page-header title="Edit Role" :description="$role->name" />
@endsection

@section('content')
    <form action="{{ route('admin.roles.update', $role) }}" method="POST" class="max-w-5xl">
        @csrf
        @method('PUT')
        @include('admin.rbac.roles._form', ['submitLabel' => 'Update Role'])
    </form>
@endsection
