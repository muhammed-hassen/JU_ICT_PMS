@extends('layouts.console')

@section('title', 'Create Task')

@section('content_header')
    @canvisit(route('admin.phases.show', $phase))
        <a href="{{ route('admin.phases.show', $phase) }}" class="mb-3 inline-flex items-center gap-1.5 text-[13px] font-medium text-muted-foreground no-underline hover:text-foreground hover:no-underline">
            <i data-lucide="arrow-left" class="size-4"></i>
            Back to Phase
        </a>
    @endcanvisit
    <x-ui.page-header title="Create Task" :description="'Add a new task to ' . $phase->name" />
@endsection

@section('content')
    @php
        $textareaClasses = 'block w-full rounded-lg border bg-card px-3 py-2 text-sm text-foreground shadow-xs placeholder:text-muted-foreground/70 focus:outline-none focus:ring-[3px]';
    @endphp

    <div class="grid gap-6 lg:grid-cols-3">
        <form action="{{ route('admin.phases.tasks.store', $phase) }}" method="POST" class="lg:col-span-2">
            @csrf
            <x-ui.card title="Task details">
                <div class="grid gap-5">
                    <div>
                        <x-ui.label for="title">Task Title <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.input id="title" name="title" :value="old('title')" placeholder="e.g. Design database schema" :invalid="$errors->has('title')" required />
                        @error('title')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <x-ui.label for="description">Description</x-ui.label>
                        <textarea name="description" id="description" rows="4" placeholder="Describe the task in detail"
                                  @class([$textareaClasses, 'border-input focus:border-ring focus:ring-ring/15' => ! $errors->has('description'), 'border-destructive focus:border-destructive focus:ring-destructive/15' => $errors->has('description')])>{{ old('description') }}</textarea>
                        @error('description')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <x-ui.label for="task_status_id">Status <span class="text-destructive">*</span></x-ui.label>
                            <x-ui.select name="task_status_id" id="task_status_id" :invalid="$errors->has('task_status_id')" required>
                                <option value="">Select Status</option>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status->id }}" {{ old('task_status_id') == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                                @endforeach
                            </x-ui.select>
                            @error('task_status_id')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <x-ui.label for="task_priority_id">Priority</x-ui.label>
                            <x-ui.select name="task_priority_id" id="task_priority_id" :invalid="$errors->has('task_priority_id')">
                                <option value="">None</option>
                                @foreach ($priorities as $priority)
                                    <option value="{{ $priority->id }}" {{ old('task_priority_id') == $priority->id ? 'selected' : '' }}>{{ $priority->name }}</option>
                                @endforeach
                            </x-ui.select>
                            @error('task_priority_id')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <x-ui.label for="assigned_to">Assign To</x-ui.label>
                            <x-ui.select name="assigned_to" id="assigned_to" class="select2" :invalid="$errors->has('assigned_to')">
                                <option value="">Unassigned</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}" {{ old('assigned_to') == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </x-ui.select>
                            @error('assigned_to')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <x-ui.label for="deadline">Deadline</x-ui.label>
                            <x-ui.input type="date" id="deadline" name="deadline" :value="old('deadline')" :invalid="$errors->has('deadline')" />
                            @error('deadline')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-3">
                        <div>
                            <x-ui.label for="estimated_hours">Estimated Hours</x-ui.label>
                            <x-ui.input type="number" step="0.5" min="0" id="estimated_hours" name="estimated_hours" :value="old('estimated_hours')" placeholder="0.0" :invalid="$errors->has('estimated_hours')" />
                            @error('estimated_hours')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <x-ui.label for="progress_percentage">Initial Progress (%)</x-ui.label>
                            <x-ui.input type="number" step="1" min="0" max="100" id="progress_percentage" name="progress_percentage" :value="old('progress_percentage', 0)" :invalid="$errors->has('progress_percentage')" />
                            @error('progress_percentage')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <x-ui.label for="sort_order">Sort Order</x-ui.label>
                            <x-ui.input type="number" min="1" id="sort_order" name="sort_order" :value="old('sort_order', $maxOrder)" :invalid="$errors->has('sort_order')" />
                            <p class="m-0 mt-1 text-[13px] text-muted-foreground">Leave empty to add at the end</p>
                            @error('sort_order')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    @include('admin.tasks.partials.cost-fields', ['task' => new App\Models\Task])
                </div>

                <x-slot:footer>
                    <div class="ml-auto flex items-center gap-2">
                        @canvisit(route('admin.phases.show', $phase))
                            <x-ui.button variant="outline" :href="route('admin.phases.show', $phase)">Cancel</x-ui.button>
                        @endcanvisit
                        <x-ui.button type="submit">
                            <i data-lucide="check" class="size-4"></i>
                            Create Task
                        </x-ui.button>
                    </div>
                </x-slot:footer>
            </x-ui.card>
        </form>

        <div class="grid content-start gap-6">
            <x-ui.card title="Phase Information">
                <dl class="m-0 grid gap-4">
                    <div>
                        <dt class="text-[13px] font-normal text-muted-foreground">Phase Name</dt>
                        <dd class="m-0 mt-1 font-medium">{{ $phase->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-[13px] font-normal text-muted-foreground">Project</dt>
                        <dd class="m-0 mt-1 font-medium">
                            <a href="{{ route('admin.projects.show', $phase->project) }}" class="text-primary no-underline hover:underline">{{ $phase->project->name }}</a>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-[13px] font-normal text-muted-foreground">Status</dt>
                        <dd class="m-0 mt-1.5"><x-ui.badge :variant="$phase->statusColor">{{ $phase->status?->name ?? 'Not Started' }}</x-ui.badge></dd>
                    </div>
                    <div>
                        <dt class="text-[13px] font-normal text-muted-foreground">Progress</dt>
                        <dd class="m-0 mt-1.5"><x-ui.progress :value="$phase->progress_percentage ?? 0" /></dd>
                    </div>
                    <div>
                        <dt class="text-[13px] font-normal text-muted-foreground">Tasks</dt>
                        <dd class="tabular m-0 mt-1 font-medium">{{ $phase->tasks->count() }}</dd>
                    </div>
                </dl>
            </x-ui.card>

            <x-ui.card title="Quick Tips">
                <ul class="m-0 grid list-none gap-2.5 p-0 text-sm">
                    <li class="flex items-start gap-2"><i data-lucide="circle-check" class="mt-0.5 size-4 shrink-0 text-success"></i>Tasks can be assigned to team members</li>
                    <li class="flex items-start gap-2"><i data-lucide="circle-check" class="mt-0.5 size-4 shrink-0 text-success"></i>Set priorities to organize work</li>
                    <li class="flex items-start gap-2"><i data-lucide="circle-check" class="mt-0.5 size-4 shrink-0 text-success"></i>Track progress with percentage</li>
                    <li class="flex items-start gap-2"><i data-lucide="circle-check" class="mt-0.5 size-4 shrink-0 text-success"></i>Due dates help with deadlines</li>
                </ul>
            </x-ui.card>
        </div>
    </div>
@endsection
