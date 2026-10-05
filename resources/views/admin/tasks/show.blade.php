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
                    <dt class="{{ $label }}">Assignees</dt>
                    <dd class="m-0 mt-1">
                        @if ($task->assignedUsers->isNotEmpty())
                            <div class="flex flex-col gap-1.5">
                                @foreach ($task->assignedUsers as $assignee)
                                    <div class="flex items-center gap-2">
                                        <span class="flex size-6 shrink-0 items-center justify-center rounded-full bg-primary/10 text-[10px] font-semibold text-ju-blue-700">
                                            {{ \Illuminate\Support\Str::of($assignee->name)->explode(' ')->take(2)->map(fn($p) => mb_substr($p,0,1))->implode('') }}
                                        </span>
                                        <span class="text-[13px] font-medium">{{ $assignee->name }}</span>
                                        @if ($task->assigned_to === $assignee->id)
                                            <x-ui.badge variant="primary">Primary</x-ui.badge>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
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
            {{-- ── Assignees card ──────────────────────────────────────────── --}}
            <x-ui.card>
                <x-slot:title>
                    Assignees
                    @if ($task->assignedUsers->isNotEmpty())
                        <x-ui.badge>{{ $task->assignedUsers->count() }}</x-ui.badge>
                    @endif
                </x-slot:title>

                @php
                    $assignedIds = $task->assignedUsers->pluck('id')->toArray();
                    $unassignedPool = collect($availableAssignees ?? [])->reject(fn($u) => in_array($u->id, $assignedIds));
                @endphp

                {{-- Current assignees list --}}
                @if ($task->assignedUsers->isNotEmpty())
                    <ul class="m-0 -mx-1 mb-3 list-none divide-y divide-border/60 p-0">
                        @foreach ($task->assignedUsers as $assignee)
                            <li class="flex items-center gap-2.5 px-1 py-2 first:pt-0">
                                {{-- Avatar --}}
                                <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-primary/10 text-[11px] font-semibold text-ju-blue-700">
                                    {{ \Illuminate\Support\Str::of($assignee->name)->explode(' ')->take(2)->map(fn($p) => mb_substr($p,0,1))->implode('') }}
                                </span>
                                {{-- Name + primary badge --}}
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-[13px] font-medium text-foreground">{{ $assignee->name }}</span>
                                    @if ($task->assigned_to === $assignee->id)
                                        <span class="text-[11px] text-muted-foreground">Primary</span>
                                    @endif
                                </span>
                                {{-- Unassign button --}}
                                @canvisit(route('admin.tasks.unassign', $task), 'POST')
                                    <form action="{{ route('admin.tasks.unassign', $task) }}" method="POST" class="m-0 shrink-0">
                                        @csrf
                                        <input type="hidden" name="user_id" value="{{ $assignee->id }}">
                                        <button type="submit"
                                                title="Remove {{ $assignee->name }}"
                                                onclick="return confirm('Remove {{ addslashes($assignee->name) }} from this task?')"
                                                class="inline-flex size-7 items-center justify-center rounded-md border-0 bg-transparent text-muted-foreground hover:bg-destructive/10 hover:text-destructive">
                                            <i data-lucide="x" class="size-3.5"></i>
                                            <span class="sr-only">Remove {{ $assignee->name }}</span>
                                        </button>
                                    </form>
                                @endcanvisit
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="mb-3 mt-0 text-[13px] text-muted-foreground">No one assigned yet.</p>
                @endif

                {{-- Add assignee --}}
                @canvisit(route('admin.tasks.assign', $task), 'POST')
                    @if ($unassignedPool->isNotEmpty())
                        <form action="{{ route('admin.tasks.assign', $task) }}" method="POST"
                              class="flex gap-2 border-t border-border pt-3">
                            @csrf
                            <label for="user_id" class="sr-only">Add assignee</label>
                            <x-ui.select id="user_id" name="user_id" class="flex-1">
                                <option value="">Add assignee…</option>
                                @foreach ($unassignedPool as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </x-ui.select>
                            <x-ui.button type="submit" variant="outline">
                                <i data-lucide="user-plus" class="size-4"></i>
                                <span class="sr-only">Add</span>
                            </x-ui.button>
                        </form>
                    @else
                        <p class="mt-0 text-[13px] text-muted-foreground border-t border-border pt-3">All available users are assigned.</p>
                    @endif
                @endcanvisit
            </x-ui.card>

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
