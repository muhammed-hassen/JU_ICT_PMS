@extends('layouts.console')

@section('title', 'Create Project')

@section('content_header')
    <a href="{{ route('admin.projects.index') }}" class="mb-3 inline-flex items-center gap-1.5 text-[13px] font-medium text-muted-foreground no-underline hover:text-foreground hover:no-underline">
        <i data-lucide="arrow-left" class="size-4"></i>
        Projects
    </a>
    <x-ui.page-header title="Create Project" description="Add a new project." />
@endsection

@section('content')
    <form action="{{ route('admin.projects.store') }}" method="POST">
        @csrf
        @include('admin.projects._form', ['submitLabel' => 'Create Project'])
    </form>
@endsection
