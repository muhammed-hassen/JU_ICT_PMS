@extends('layouts.console')

@section('title', 'Edit Phase')

@section('content_header')
    <x-ui.page-header :title="'Edit Phase: ' . $phase->name" :description="$phase->project->name ?? null">
        @canvisit(route('admin.phases.show', $phase))
            <x-ui.button variant="outline" :href="route('admin.phases.show', $phase)">
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
        <form action="{{ route('admin.phases.update', $phase) }}" method="POST" class="lg:col-span-2">
            @csrf
            @method('PUT')
            <x-ui.card title="Phase Details">
                <div class="space-y-5">
                    <div>
                        <x-ui.label for="name">Phase Name <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.input type="text" name="name" id="name" placeholder="Enter phase name" :value="old('name', $phase->name)" :invalid="$errors->has('name')" required />
                        @error('name')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <x-ui.label for="description">Description</x-ui.label>
                        <textarea name="description" id="description" rows="4" placeholder="Describe the phase"
                                  class="{{ $textarea }} {{ $errors->has('description') ? $textareaBad : $textareaOk }}">{{ old('description', $phase->description) }}</textarea>
                        @error('description')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <x-ui.label for="phase_status_id">Status <span class="text-destructive">*</span></x-ui.label>
                            <x-ui.select name="phase_status_id" id="phase_status_id" :invalid="$errors->has('phase_status_id')" required>
                                <option value="">Select Status</option>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status->id }}" {{ old('phase_status_id', $phase->phase_status_id) == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                                @endforeach
                            </x-ui.select>
                            @error('phase_status_id')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <x-ui.label for="sort_order">Sort Order</x-ui.label>
                            <x-ui.input type="number" name="sort_order" id="sort_order" min="1" :value="old('sort_order', $phase->sort_order)" :invalid="$errors->has('sort_order')" required />
                            @error('sort_order')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <x-ui.label for="start_date">Planned Start Date</x-ui.label>
                            <x-ui.input type="date" name="start_date" id="start_date" :value="old('start_date', $phase->start_date ? $phase->start_date->format('Y-m-d') : '')" :invalid="$errors->has('start_date')" />
                            @error('start_date')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <x-ui.label for="end_date">Planned End Date</x-ui.label>
                            <x-ui.input type="date" name="end_date" id="end_date" :value="old('end_date', $phase->end_date ? $phase->end_date->format('Y-m-d') : '')" :invalid="$errors->has('end_date')" />
                            @error('end_date')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                <x-slot:footer>
                    <div class="flex w-full justify-end gap-2">
                        @canvisit(route('admin.phases.show', $phase))
                            <x-ui.button variant="outline" :href="route('admin.phases.show', $phase)">Cancel</x-ui.button>
                        @endcanvisit
                        <x-ui.button type="submit">
                            <i data-lucide="save" class="size-4"></i>
                            Update Phase
                        </x-ui.button>
                    </div>
                </x-slot:footer>
            </x-ui.card>
        </form>

        <x-ui.card title="Phase Info">
            <dl class="m-0 divide-y divide-border">
                <div class="py-2.5 first:pt-0">
                    <dt class="{{ $label }}">Progress</dt>
                    <dd class="m-0 mt-1.5"><x-ui.progress :value="$phase->progress_percentage" /></dd>
                </div>
                <div class="flex items-center justify-between py-2.5">
                    <dt class="{{ $label }}">Total Tasks</dt>
                    <dd class="tabular m-0 font-semibold">{{ $phase->tasks->count() }}</dd>
                </div>
                <div class="flex items-center justify-between py-2.5">
                    <dt class="{{ $label }}">Completed Tasks</dt>
                    <dd class="tabular m-0 font-semibold">{{ $phase->tasks->where('progress_percentage', 100)->count() }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4 py-2.5">
                    <dt class="{{ $label }}">Project</dt>
                    <dd class="m-0 text-right font-medium">{{ $phase->project->name }}</dd>
                </div>
                <div class="flex items-center justify-between py-2.5 last:pb-0">
                    <dt class="{{ $label }}">Created</dt>
                    <dd class="m-0">{{ $phase->created_at->diffForHumans() }}</dd>
                </div>
            </dl>
        </x-ui.card>
    </div>
@endsection
