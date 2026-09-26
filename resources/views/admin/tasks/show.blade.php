@extends('layouts.console')

@section('title', $task->title)

@section('content_header')
    <a href="{{ route('admin.tasks.index') }}" class="mb-3 inline-flex items-center gap-1.5 text-[13px] font-medium text-muted-foreground no-underline hover:text-foreground hover:no-underline">
        <i data-lucide="arrow-left" class="size-4"></i>
        Tasks
    </a>
    <x-ui.page-header :title="$task->title" :description="($task->phase->project->name ?? '') . ' · ' . ($task->phase->name ?? '')">
        @canvisit(route('admin.tasks.edit', $task))
            <x-ui.button variant="outline" :href="route('admin.tasks.edit', $task)">
                <i data-lucide="pencil" class="size-4"></i>
                Edit
            </x-ui.button>
        @endcanvisit
    </x-ui.page-header>
@endsection

@section('content')
    @php
        $priorityVariant = ['Critical' => 'destructive', 'High' => 'warning'][$task->priority->name ?? ''] ?? 'neutral';
        $label = 'text-[13px] font-normal text-muted-foreground';
    @endphp

    <div class="grid items-start gap-5 lg:grid-cols-3">
        {{-- Main --}}
        <div class="lg:col-span-2">
        <x-ui.card title="Details">
            <x-slot:actions>
                <x-ui.badge :variant="$task->status_color">{{ $task->status->name ?? 'Unknown' }}</x-ui.badge>
                <x-ui.badge :variant="$priorityVariant">{{ $task->priority->name ?? 'None' }}</x-ui.badge>
            </x-slot:actions>

            <div>
                <p class="m-0 {{ $label }}">Description</p>
                <p class="m-0 mt-1 max-w-3xl whitespace-pre-line">{{ $task->description ?? 'No description' }}</p>
            </div>

            <dl class="m-0 mt-6 grid gap-x-8 gap-y-5 sm:grid-cols-2 xl:grid-cols-3">
                <div>
                    <dt class="{{ $label }}">Project</dt>
                    <dd class="m-0 mt-1">
                        <a href="{{ route('admin.projects.show', $task->phase->project_id) }}" class="font-medium">{{ $task->phase->project->name }}</a>
                    </dd>
                </div>
                <div>
                    <dt class="{{ $label }}">Phase</dt>
                    <dd class="m-0 mt-1 font-medium">{{ $task->phase->name }}</dd>
                </div>
                <div>
                    <dt class="{{ $label }}">Assignee</dt>
                    <dd class="m-0 mt-1 flex items-center gap-2 font-medium">
                        @if ($task->assignee)
                            <span class="flex size-7 items-center justify-center rounded-full bg-primary/10 text-[11px] font-semibold text-ju-blue-700">
                                {{ \Illuminate\Support\Str::of($task->assignee->name)->explode(' ')->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode('') }}
                            </span>
                            {{ $task->assignee->name }}
                        @else
                            <span class="font-normal text-muted-foreground">Unassigned</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="{{ $label }}">Start date</dt>
                    <dd class="tabular m-0 mt-1 font-medium">{{ $task->start_date ? $task->start_date->format('M d, Y') : 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="{{ $label }}">Deadline</dt>
                    <dd class="m-0 mt-1">
                        @if ($task->deadline)
                            @if ($task->isOverdue())
                                <span class="tabular font-medium text-destructive">{{ $task->deadline->format('M d, Y') }}</span>
                                <span class="block text-xs text-destructive">{{ $task->days_overdue }} days overdue</span>
                            @else
                                <span class="tabular font-medium">{{ $task->deadline->format('M d, Y') }}</span>
                                @if ($task->days_remaining)
                                    <span class="block text-xs text-muted-foreground">{{ $task->days_remaining }} days remaining</span>
                                @endif
                            @endif
                        @else
                            <span class="text-muted-foreground">No deadline set</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="{{ $label }}">Estimated hours</dt>
                    <dd class="tabular m-0 mt-1 font-medium">{{ $task->estimated_hours ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="{{ $label }}">Estimated cost</dt>
                    <dd class="tabular m-0 mt-1 font-medium">{{ $task->estimated_cost !== null ? number_format($task->estimated_cost, 2) . ' ETB' : 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="{{ $label }}">Actual cost</dt>
                    <dd class="tabular m-0 mt-1 font-medium">{{ $task->actual_cost !== null ? number_format($task->actual_cost, 2) . ' ETB' : 'N/A' }}</dd>
                </div>
            </dl>

            @if ($task->resources)
                <div class="mt-6">
                    <p class="m-0 {{ $label }}">Resources</p>
                    <p class="m-0 mt-1 max-w-3xl whitespace-pre-line">{{ $task->resources }}</p>
                </div>
            @endif

            <div class="mt-6">
                <p class="m-0 mb-1.5 {{ $label }}">Progress</p>
                <x-ui.progress :value="$task->progress_percentage" />
            </div>

            @canvisit(route('admin.tasks.update-status', $task), 'PATCH')
                <form action="{{ route('admin.tasks.update-status', $task) }}" method="POST" class="mt-6 border-t border-border pt-5">
                    @csrf
                    @method('PATCH')
                    <x-ui.label for="task_status_id">Update status</x-ui.label>
                    <div class="flex max-w-md gap-2">
                        <x-ui.select id="task_status_id" name="task_status_id">
                            @foreach ($task->statusOptionsFor(auth()->user()) as $status)
                                <option value="{{ $status->id }}" {{ $task->task_status_id == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                            @endforeach
                        </x-ui.select>
                        <x-ui.button type="submit">Update</x-ui.button>
                    </div>
                </form>
            @endcanvisit

            <x-slot:footer>
                <span class="text-muted-foreground">
                    Created {{ $task->created_at->diffForHumans() }} by {{ $task->creator->name ?? 'Unknown' }}
                    @if ($task->updated_at != $task->created_at)
                        · Updated {{ $task->updated_at->diffForHumans() }}
                    @endif
                </span>
            </x-slot:footer>
        </x-ui.card>

        @include('admin.tasks.partials.subtasks')
        </div>

        {{-- Side --}}
        <div class="space-y-5">
            @canvisit(route('admin.tasks.assign', $task), 'POST')
                <x-ui.card title="Assignment">
                    <form action="{{ route('admin.tasks.assign', $task) }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <x-ui.label for="user_id">Assign to</x-ui.label>
                            <x-ui.select id="user_id" name="user_id">
                                <option value="">Select user</option>
                                @foreach ($availableAssignees ?? [] as $user)
                                    <option value="{{ $user->id }}" {{ $task->assigned_to == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </x-ui.select>
                        </div>
                        <x-ui.button type="submit" class="w-full">
                            <i data-lucide="user-plus" class="size-4"></i>
                            Assign task
                        </x-ui.button>
                    </form>
                </x-ui.card>
            @endcanvisit

            <x-ui.card title="Summary">
                <dl class="m-0 divide-y divide-border">
                    <div class="flex items-center justify-between py-2.5 first:pt-0">
                        <dt class="{{ $label }}">Status</dt>
                        <dd class="m-0"><x-ui.badge :variant="$task->status_color">{{ $task->status->name ?? 'Unknown' }}</x-ui.badge></dd>
                    </div>
                    <div class="flex items-center justify-between py-2.5">
                        <dt class="{{ $label }}">Priority</dt>
                        <dd class="m-0"><x-ui.badge :variant="$priorityVariant">{{ $task->priority->name ?? 'None' }}</x-ui.badge></dd>
                    </div>
                    <div class="flex items-center justify-between py-2.5">
                        <dt class="{{ $label }}">Progress</dt>
                        <dd class="tabular m-0 font-semibold">{{ $task->progress_percentage }}%</dd>
                    </div>
                    <div class="flex items-center justify-between py-2.5 last:pb-0">
                        <dt class="{{ $label }}">Completed</dt>
                        <dd class="m-0">
                            @if ($task->completed_at)
                                {{ $task->completed_at->diffForHumans() }}
                            @else
                                <span class="text-muted-foreground">Not completed</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </x-ui.card>

            <x-ui.card title="Phase progress" :description="$task->phase->tasks->count() . ' tasks in this phase'">
                <x-ui.progress :value="$task->phase->progress_percentage" />
                <x-slot:footer>
                    <span class="text-muted-foreground">{{ $task->phase->name }}</span>
                    <x-ui.button variant="outline" size="sm" :href="route('admin.phases.show', $task->phase)">View phase</x-ui.button>
                </x-slot:footer>
            </x-ui.card>
        </div>
    </div>
@endsection
