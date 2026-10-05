{{-- Team Leader dashboard: the leader's teams, what needs review, and who is overloaded. --}}
@extends('layouts.console')
@section('title', 'Dashboard')

@section('content_header')
    <x-ui.page-header title="Team dashboard" :description="$teamNames->isEmpty() ? 'You do not lead or belong to a team yet.' : 'Your teams: ' . $teamNames->join(', ', ' and ') . '.'">
        <x-ui.button variant="outline" :href="route('admin.tasks.kanban')">
            <i data-lucide="square-kanban" class="size-4"></i>
            Task board
        </x-ui.button>
        @can('create-project')
            <x-ui.button :href="route('admin.projects.create')">
                <i data-lucide="plus" class="size-4"></i>
                New project
            </x-ui.button>
        @endcan
    </x-ui.page-header>
@endsection

@section('content')
    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat-card label="Team projects" :value="$projects->count()" icon="folder-kanban" :href="route('admin.projects.index')" link-text="View projects" />
        <x-ui.stat-card label="Open tasks" :value="$tasksCount - $completedTasks" icon="list-todo" :href="route('admin.tasks.index')" link-text="View tasks" />
        <x-ui.stat-card label="Waiting for your review" :value="$reviewTasks" icon="eye" />
        <x-ui.stat-card label="Overdue tasks" :value="$overdueTasks" icon="triangle-alert" :href="route('admin.tasks.index', ['overdue' => 1])" link-text="View overdue" />
    </div>

    <div class="mt-6 grid gap-5 lg:grid-cols-2">
        <x-ui.card title="Waiting for your review" description="Tasks your team moved to Under Review." flush>
            @include('dashboards.partials.task-list', ['tasks' => $awaitingReview, 'empty' => 'Nothing is waiting for review.'])
        </x-ui.card>

        <x-ui.card title="Overdue in your teams" description="Missed deadlines, oldest first." flush>
            @include('dashboards.partials.task-list', ['tasks' => $overdueList, 'empty' => 'Nothing is overdue.'])
        </x-ui.card>
    </div>

    <x-ui.card class="mt-6" title="Team workload" description="Open, overdue and finished tasks per person." flush>
        @if ($members->isEmpty())
            <x-ui.empty-state icon="users" title="No team members yet" description="Members added to your team will show up here." />
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted text-left text-[11px] uppercase tracking-[0.08em] text-muted-foreground">
                            <th class="px-5 py-2.5 font-semibold">Person</th>
                            <th class="px-5 py-2.5 text-right font-semibold">Open</th>
                            <th class="px-5 py-2.5 text-right font-semibold">Overdue</th>
                            <th class="px-5 py-2.5 text-right font-semibold">Done</th>
                            @can('create-conversation')<th class="px-5 py-2.5"><span class="sr-only">Message</span></th>@endcan
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($members as $row)
                            <tr class="border-b border-border/70 transition-colors duration-200 last:border-0 hover:bg-background">
                                <td class="px-5 py-3">
                                    <span class="block font-semibold text-foreground">{{ $row['user']->name }}</span>
                                    <span class="block text-[13px] text-muted-foreground">{{ $row['user']->email }}</span>
                                </td>
                                <td class="tabular px-5 py-3 text-right">{{ $row['open'] }}</td>
                                <td @class(['tabular px-5 py-3 text-right', 'font-semibold text-destructive' => $row['overdue'] > 0])>{{ $row['overdue'] }}</td>
                                <td class="tabular px-5 py-3 text-right">{{ $row['done'] }}</td>
                                @can('create-conversation')
                                    <td class="px-5 py-3 text-right">
                                        <x-ui.button variant="ghost" size="sm" :href="route('messages.create', ['to' => [$row['user']->id]])">
                                            <i data-lucide="message-square" class="size-4"></i>
                                            Message
                                        </x-ui.button>
                                    </td>
                                @endcan
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-ui.card>

    <x-ui.card class="mt-6" title="Team projects" description="Progress of the projects your teams work on." flush>
        @if ($projects->isEmpty())
            <x-ui.empty-state icon="folder-kanban" title="No projects yet" />
        @else
            <ul class="m-0 list-none divide-y divide-border p-0">
                @foreach ($projects as $project)
                    <li class="flex items-center gap-4 px-5 py-3">
                        <div class="min-w-0 flex-1">
                            <a href="{{ route('admin.projects.show', $project) }}" class="block truncate font-semibold text-foreground no-underline hover:text-primary hover:no-underline">{{ $project->name }}</a>
                            <p class="m-0 text-[13px] text-muted-foreground">{{ ucfirst($project->status ?? 'draft') }} · {{ $project->phases->count() }} phases @if ($project->end_date) · ends {{ $project->end_date->format('M j, Y') }} @endif</p>
                        </div>
                        <x-ui.progress class="w-40 shrink-0" :value="$project->progress_percentage ?? 0" />
                    </li>
                @endforeach
            </ul>
        @endif
    </x-ui.card>
@endsection
