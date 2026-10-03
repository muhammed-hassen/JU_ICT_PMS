@extends('layouts.console')

@section('title', 'Edit Permission')

@section('content_header')
    @canvisit(route('admin.permissions.index'))
        <a href="{{ route('admin.permissions.index') }}" class="mb-3 inline-flex items-center gap-1.5 text-[13px] font-medium text-muted-foreground no-underline hover:text-foreground hover:no-underline">
            <i data-lucide="arrow-left" class="size-4"></i>
            Permissions
        </a>
    @endcanvisit
    <x-ui.page-header title="Edit Permission" :description="$permission->name" />
@endsection

@section('content')
    <form action="{{ route('admin.permissions.update', $permission) }}" method="POST" class="max-w-2xl">
        @csrf
        @method('PUT')
        @include('admin.rbac.permissions._form', ['submitLabel' => 'Update Permission'])
    </form>
@endsection
