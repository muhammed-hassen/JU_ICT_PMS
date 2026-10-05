@extends('layouts.console')

@section('title', 'Edit Director')

@section('content_header')
    @canvisit(route('admin.organization.directors.show', $director))
        <a href="{{ route('admin.organization.directors.show', $director) }}" class="mb-3 inline-flex items-center gap-1.5 text-[13px] font-medium text-muted-foreground no-underline hover:text-foreground hover:no-underline">
            <i data-lucide="arrow-left" class="size-4"></i>
            {{ $director->name }}
        </a>
    @endcanvisit
    <x-ui.page-header title="Edit Director" :description="$director->name" />
@endsection

@section('content')
    <form action="{{ route('admin.organization.directors.update', $director) }}" method="POST">
        @csrf
        @method('PUT')
        @include('admin.organization.directors._form', ['submitLabel' => 'Update Director'])
    </form>
@endsection
