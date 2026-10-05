@extends('layouts.console')

@section('title', 'Create Permission')

@section('content_header')
    @canvisit(route('admin.permissions.index'))
        <a href="{{ route('admin.permissions.index') }}" class="mb-3 inline-flex items-center gap-1.5 text-[13px] font-medium text-muted-foreground no-underline hover:text-foreground hover:no-underline">
            <i data-lucide="arrow-left" class="size-4"></i>
            Permissions
        </a>
    @endcanvisit
    <x-ui.page-header title="Create Permission" description="Add a permission to the catalog." />
@endsection

@section('content')
    <form action="{{ route('admin.permissions.store') }}" method="POST" class="max-w-2xl">
        @csrf
        @include('admin.rbac.permissions._form', ['submitLabel' => 'Create Permission'])
    </form>
@endsection
