@extends('layouts.console')

@section('title', 'New Template')

@section('content_header')
    <a href="{{ route('admin.templates.index') }}" class="mb-3 inline-flex items-center gap-1.5 text-[13px] font-medium text-muted-foreground no-underline hover:text-foreground hover:no-underline">
        <i data-lucide="arrow-left" class="size-4"></i>
        Project Templates
    </a>
    <x-ui.page-header title="Create Template" description="Build the default phase and task structure" />
@endsection

@section('content')
    <form action="{{ route('admin.templates.store') }}" method="POST">
        @csrf
        @include('admin.templates._form', ['submitLabel' => $submitLabel])
    </form>
@endsection
