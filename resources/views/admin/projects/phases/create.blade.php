@extends('layouts.console')

@section('title', 'Create Phase')

@section('content_header')
    <x-ui.page-header title="Create New Phase" :description="'for ' . $project->name">
        @canvisit(route('admin.projects.phases.index', $project))
            <x-ui.button variant="outline" :href="route('admin.projects.phases.index', $project)">
                <i data-lucide="arrow-left" class="size-4"></i>
                Back
            </x-ui.button>
        @endcanvisit
    </x-ui.page-header>
@endsection

@section('content')
    @php
        $textarea = 'block w-full rounded-lg border bg-card px-3 py-2 text-sm shadow-xs focus:outline-none focus:ring-[3px]';
        $textareaOk = 'border-input focus:border-ring focus:ring-ring/15';
        $textareaBad = 'border-destructive focus:border-destructive focus:ring-destructive/15';
        $label = 'text-[13px] font-normal text-muted-foreground';
    @endphp

    <div class="grid items-start gap-5 lg:grid-cols-3">
        <form action="{{ route('admin.projects.phases.store', $project) }}" method="POST" class="lg:col-span-2">
            @csrf
            <x-ui.card title="Phase Details">
                <div class="space-y-5">
                    <div>
                        <x-ui.label for="name">Phase Name <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.input type="text" name="name" id="name" placeholder="Enter phase name (e.g., Planning, Development)" :value="old('name')" :invalid="$errors->has('name')" required />
                        @error('name')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <x-ui.label for="description">Description</x-ui.label>
                        <textarea name="description" id="description" rows="4" placeholder="Describe the phase objectives and scope"
                                  class="{{ $textarea }} {{ $errors->has('description') ? $textareaBad : $textareaOk }}">{{ old('description') }}</textarea>
                        @error('description')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <x-ui.label for="phase_status_id">Status <span class="text-destructive">*</span></x-ui.label>
                            <x-ui.select name="phase_status_id" id="phase_status_id" :invalid="$errors->has('phase_status_id')" required>
                                <option value="">Select Status</option>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status->id }}" {{ old('phase_status_id', $phase->phase_status_id ?? '') == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                                @endforeach
                            </x-ui.select>
                            @error('phase_status_id')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <x-ui.label for="sort_order">Sort Order</x-ui.label>
                            <x-ui.input type="number" name="sort_order" id="sort_order" min="1" :value="old('sort_order', $maxOrder)" :invalid="$errors->has('sort_order')" />
                            <p class="m-0 mt-1 text-[13px] text-muted-foreground">Leave empty to add at the end</p>
                            @error('sort_order')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <x-ui.label for="start_date">Planned Start Date</x-ui.label>
                            <x-ui.input type="date" name="start_date" id="start_date" :value="old('start_date')" :invalid="$errors->has('start_date')" />
                            @error('start_date')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <x-ui.label for="end_date">Planned End Date</x-ui.label>
                            <x-ui.input type="date" name="end_date" id="end_date" :value="old('end_date')" :invalid="$errors->has('end_date')" />
                            @error('end_date')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                <x-slot:footer>
                    <div class="flex w-full justify-end gap-2">
                        @canvisit(route('admin.projects.phases.index', $project))
                            <x-ui.button variant="outline" :href="route('admin.projects.phases.index', $project)">Cancel</x-ui.button>
                        @endcanvisit
                        <x-ui.button type="submit">
                            <i data-lucide="save" class="size-4"></i>
                            Create Phase
                        </x-ui.button>
                    </div>
                </x-slot:footer>
            </x-ui.card>
        </form>

        <div class="space-y-5">
            <x-ui.card title="Project Info">
                <dl class="m-0 divide-y divide-border">
                    <div class="flex items-center justify-between gap-4 py-2.5 first:pt-0">
                        <dt class="{{ $label }}">Project Name</dt>
                        <dd class="m-0 text-right font-medium">{{ $project->name }}</dd>
                    </div>
                    <div class="flex items-center justify-between py-2.5">
                        <dt class="{{ $label }}">Total Phases</dt>
                        <dd class="tabular m-0 font-semibold">{{ $project->phases->count() }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4 py-2.5">
                        <dt class="{{ $label }}">Created By</dt>
                        <dd class="m-0 text-right">{{ $project->creator?->name ?? 'N/A' }}</dd>
                    </div>
                    <div class="flex items-center justify-between py-2.5 last:pb-0">
                        <dt class="{{ $label }}">Status</dt>
                        <dd class="m-0">
                            <x-ui.badge :variant="$project->status === 'active' ? 'success' : 'neutral'">{{ ucfirst($project->status ?? 'Draft') }}</x-ui.badge>
                        </dd>
                    </div>
                </dl>
            </x-ui.card>

            <x-ui.card title="Tips">
                <ul class="m-0 list-none space-y-2.5 p-0 text-sm">
                    @foreach (['Each phase can have multiple tasks', 'Phases can be reordered', 'Progress is automatically calculated', 'You can assign status to each phase'] as $tip)
                        <li class="flex items-start gap-2.5">
                            <i data-lucide="circle-check" class="mt-0.5 size-4 shrink-0 text-muted-foreground"></i>
                            <span>{{ $tip }}</span>
                        </li>
                    @endforeach
                </ul>
            </x-ui.card>
        </div>
    </div>
@endsection
