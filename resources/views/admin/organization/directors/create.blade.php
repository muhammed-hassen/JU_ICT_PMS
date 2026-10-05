@extends('layouts.console')

@section('title', 'Create Director')

@section('content_header')
    @canvisit(route('admin.organization.directors.index'))
        <a href="{{ route('admin.organization.directors.index') }}" class="mb-3 inline-flex items-center gap-1.5 text-[13px] font-medium text-muted-foreground no-underline hover:text-foreground hover:no-underline">
            <i data-lucide="arrow-left" class="size-4"></i>
            Directors
        </a>
    @endcanvisit
    <x-ui.page-header title="Create Director" description="Add a new member of the ICT leadership." />
@endsection

@section('content')
    <form action="{{ route('admin.organization.directors.store') }}" method="POST">
        @csrf
        @include('admin.organization.directors._form', ['submitLabel' => 'Create Director'])
    </form>
@endsection
