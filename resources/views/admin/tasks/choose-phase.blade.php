{{-- Step one of "New task": every task lives in a phase of a project, so pick the phase. --}}
@extends('layouts.console')

@section('title', 'Create Task')

@section('content_header')
    <x-ui.page-header title="Create Task" description="Every task belongs to a phase of a project. Choose where this one goes." />
@endsection

@section('content')
    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($projects as $project)
            <x-ui.card :title="$project->name" :description="$project->phases->count() . ' ' . Str::plural('phase', $project->phases->count())" flush>
                @if ($project->phases->isEmpty())
                    <div class="px-5 py-4 text-sm text-muted-foreground">
                        No phases yet, so no tasks can go here.
                        @canvisit(route('admin.projects.phases.create', $project))
                            <a href="{{ route('admin.projects.phases.create', $project) }}" class="font-medium">Add a phase</a>
                        @endcanvisit
                    </div>
                @else
                    <ul class="m-0 list-none divide-y divide-border p-0">
                        @foreach ($project->phases as $phase)
                            <li>
                                <a href="{{ route('admin.phases.tasks.create', $phase) }}"
                                   class="flex items-center justify-between gap-3 px-5 py-3 text-sm text-foreground no-underline hover:bg-background hover:no-underline">
                                    <span>{{ $phase->name }}</span>
                                    <span class="inline-flex items-center gap-1 text-[13px] font-medium text-primary">
                                        Add task here
                                        <i data-lucide="arrow-right" class="size-4"></i>
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-ui.card>
        @endforeach
    </div>
@endsection
