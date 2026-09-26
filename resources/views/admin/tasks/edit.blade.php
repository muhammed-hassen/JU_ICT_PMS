@extends('layouts.console')

@section('title', 'Edit Task')

@section('content_header')
    <a href="{{ route('admin.tasks.show', $task) }}" class="mb-3 inline-flex items-center gap-1.5 text-[13px] font-medium text-muted-foreground no-underline hover:text-foreground hover:no-underline">
        <i data-lucide="arrow-left" class="size-4"></i>
        Back
    </a>
    <x-ui.page-header :title="'Edit Task: ' . $task->title" :description="($task->phase->project->name ?? '') . ' · ' . ($task->phase->name ?? '')" />
@endsection

@section('content')
    @php
        $textarea = 'block w-full rounded-lg border bg-card px-3 py-2 text-sm shadow-xs focus:outline-none focus:ring-[3px]';
        $textareaOk = 'border-input focus:border-ring focus:ring-ring/15';
        $textareaBad = 'border-destructive focus:border-destructive focus:ring-destructive/15';
        $label = 'text-[13px] font-normal text-muted-foreground';
        $deadlineStatus = $task->deadline ? $task->getDeadlineStatusAttribute() : null;
    @endphp

    <div class="grid items-start gap-5 lg:grid-cols-3">
        <form action="{{ route('admin.tasks.update', $task) }}" method="POST" class="lg:col-span-2">
            @csrf
            @method('PUT')
            <x-ui.card title="Task Details">
                <div class="space-y-5">
                    <div>
                        <x-ui.label for="title">Task Title <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.input type="text" name="title" id="title" placeholder="Enter task title" :value="old('title', $task->title)" :invalid="$errors->has('title')" required />
                        @error('title')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <x-ui.label for="description">Description</x-ui.label>
                        <textarea name="description" id="description" rows="4" placeholder="Describe the task in detail"
                                  class="{{ $textarea }} {{ $errors->has('description') ? $textareaBad : $textareaOk }}">{{ old('description', $task->description) }}</textarea>
                        @error('description')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <x-ui.label for="task_status_id">Status <span class="text-destructive">*</span></x-ui.label>
                            <x-ui.select name="task_status_id" id="task_status_id" :invalid="$errors->has('task_status_id')" required>
                                <option value="">Select Status</option>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status->id }}" {{ old('task_status_id', $task->task_status_id) == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                                @endforeach
                            </x-ui.select>
                            @error('task_status_id')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <x-ui.label for="task_priority_id">Priority</x-ui.label>
                            <x-ui.select name="task_priority_id" id="task_priority_id" :invalid="$errors->has('task_priority_id')">
                                <option value="">Select Priority</option>
                                @foreach ($priorities as $priority)
                                    <option value="{{ $priority->id }}" {{ old('task_priority_id', $task->task_priority_id) == $priority->id ? 'selected' : '' }}>{{ $priority->name }}</option>
                                @endforeach
                            </x-ui.select>
                            @error('task_priority_id')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- Read-only: worked out from priority and deadline. --}}
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <p class="m-0 mb-1.5 text-[13px] font-semibold text-foreground">Priority Score</p>
                            <div class="flex h-10 items-center justify-between gap-3 rounded-lg border border-border bg-muted px-3 text-sm">
                                <span class="tabular font-medium">{{ $task->calculatePriorityScore() }} / 100</span>
                                <x-ui.badge :variant="$task->getPriorityBadgeColorAttribute()">{{ $task->getPriorityLevelAttribute() }}</x-ui.badge>
                            </div>
                            <p class="m-0 mt-1 text-[13px] text-muted-foreground">Auto-calculated based on priority level and deadline urgency</p>
                        </div>

                        @if ($task->deadline)
                            <div>
                                <p class="m-0 mb-1.5 text-[13px] font-semibold text-foreground">Deadline Status</p>
                                <div class="flex h-10 items-center justify-between gap-3 rounded-lg border border-border bg-muted px-3 text-sm">
                                    <span class="font-medium">{{ $task->getDeadlineBadgeAttribute() }}</span>
                                    <x-ui.badge :variant="$task->getDeadlineColorAttribute()">
                                        @if ($task->isOverdue())
                                            <i data-lucide="triangle-alert" class="size-3"></i> Overdue
                                        @elseif ($deadlineStatus == 'urgent')
                                            <i data-lucide="clock" class="size-3"></i> Urgent
                                        @else
                                            <i data-lucide="calendar-check" class="size-3"></i> On Track
                                        @endif
                                    </x-ui.badge>
                                </div>
                            </div>
                        @endif
                    </div>

                    @can('assign-task')
                        <div>
                            <x-ui.label for="assigned_to">Assign To</x-ui.label>
                            <x-ui.select name="assigned_to" id="assigned_to" :invalid="$errors->has('assigned_to')">
                                <option value="">Unassigned</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}" {{ old('assigned_to', $task->assigned_to) == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </x-ui.select>
                            @error('assigned_to')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
                        </div>
                    @endcan

                    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                        <div>
                            <x-ui.label for="progress_percentage">Progress (%)</x-ui.label>
                            <x-ui.input type="number" name="progress_percentage" id="progress_percentage" min="0" max="100" :value="old('progress_percentage', $task->progress_percentage)" :invalid="$errors->has('progress_percentage')" />
                            @error('progress_percentage')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
                            <p class="m-0 mt-1 text-[13px] text-muted-foreground">0 to 100% complete</p>
                        </div>
                        <div>
                            <x-ui.label for="start_date">Start Date</x-ui.label>
                            <x-ui.input type="date" name="start_date" id="start_date" :value="old('start_date', $task->start_date ? $task->start_date->format('Y-m-d') : '')" :invalid="$errors->has('start_date')" />
                            @error('start_date')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <x-ui.label for="deadline">Deadline</x-ui.label>
                            <x-ui.input type="date" name="deadline" id="deadline" :value="old('deadline', $task->deadline ? $task->deadline->format('Y-m-d') : '')" :invalid="$errors->has('deadline')" />
                            @error('deadline')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <x-ui.label for="estimated_hours">Estimated Hours</x-ui.label>
                            <x-ui.input type="number" name="estimated_hours" id="estimated_hours" placeholder="0.00" step="0.5" min="0" :value="old('estimated_hours', $task->estimated_hours)" :invalid="$errors->has('estimated_hours')" />
                            @error('estimated_hours')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    @include('admin.tasks.partials.cost-fields')
                </div>

                <x-slot:footer>
                    <div class="flex w-full justify-end gap-2">
                        @canvisit(route('admin.tasks.show', $task))
                            <x-ui.button variant="outline" :href="route('admin.tasks.show', $task)">Cancel</x-ui.button>
                        @endcanvisit
                        <x-ui.button type="submit">
                            <i data-lucide="save" class="size-4"></i>
                            Update Task
                        </x-ui.button>
                    </div>
                </x-slot:footer>
            </x-ui.card>
        </form>

        <div class="space-y-5">
            <x-ui.card title="Task Info">
                <dl class="m-0 divide-y divide-border">
                    <div class="flex items-center justify-between py-2.5 first:pt-0">
                        <dt class="{{ $label }}">Created</dt>
                        <dd class="m-0">{{ $task->created_at->diffForHumans() }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4 py-2.5">
                        <dt class="{{ $label }}">Created By</dt>
                        <dd class="m-0 text-right font-medium">{{ $task->creator->name ?? 'Unknown' }}</dd>
                    </div>
                    <div @class(['flex items-center justify-between py-2.5', 'last:pb-0' => ! $task->completed_at])>
                        <dt class="{{ $label }}">Last Updated</dt>
                        <dd class="m-0">{{ $task->updated_at->diffForHumans() }}</dd>
                    </div>
                    @if ($task->completed_at)
                        <div class="flex items-center justify-between py-2.5 last:pb-0">
                            <dt class="{{ $label }}">Completed</dt>
                            <dd class="m-0">{{ $task->completed_at->diffForHumans() }}</dd>
                        </div>
                    @endif
                </dl>
            </x-ui.card>

            <x-ui.card title="Priority Legend" description="Score = Priority Weight + Deadline Bonus">
                <ul class="m-0 list-none space-y-2.5 p-0 text-sm">
                    @foreach ([['Critical', 'destructive', '60-100'], ['High', 'warning', '45-59'], ['Medium', 'primary', '25-44'], ['Low', 'success', '0-24']] as [$name, $variant, $range])
                        <li class="flex items-center justify-between">
                            <x-ui.badge :variant="$variant">{{ $name }}</x-ui.badge>
                            <span class="tabular text-muted-foreground">Score: {{ $range }}</span>
                        </li>
                    @endforeach
                </ul>
            </x-ui.card>
        </div>
    </div>
@endsection
