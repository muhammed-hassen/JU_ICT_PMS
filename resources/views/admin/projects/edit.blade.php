@extends('layouts.console')

@section('title', 'Edit Project')

@section('content_header')
    <a href="{{ route('admin.projects.index') }}" class="mb-3 inline-flex items-center gap-1.5 text-[13px] font-medium text-muted-foreground no-underline hover:text-foreground hover:no-underline">
        <i data-lucide="arrow-left" class="size-4"></i>
        Projects
    </a>
    <x-ui.page-header title="Edit Project" :description="$project->name" />
@endsection

@section('content')
    <form action="{{ route('admin.projects.update', $project) }}" method="POST">
        @csrf
        @method('PUT')
        @include('admin.projects._form', ['submitLabel' => 'Update Project'])
    </form>
@endsection
