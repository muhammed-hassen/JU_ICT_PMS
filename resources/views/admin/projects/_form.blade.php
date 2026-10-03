@php
    $textareaClasses = 'block w-full rounded-lg border bg-card px-3 py-2 text-sm text-foreground shadow-xs placeholder:text-muted-foreground/70 focus:outline-none focus:ring-[3px]';
@endphp

<div class="grid gap-6 lg:grid-cols-3">
    <x-ui.card title="Project details" class="lg:col-span-2">
        <div class="grid gap-5">
            <div>
                <x-ui.label for="name">Project Name <span class="text-destructive">*</span></x-ui.label>
                <x-ui.input id="name" name="name" :value="old('name', $project->name)" :invalid="$errors->has('name')" required />
                @error('name')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
            </div>

            <div>
                <x-ui.label for="description">Description</x-ui.label>
                <textarea name="description" id="description" rows="4"
                          @class([$textareaClasses, 'border-input focus:border-ring focus:ring-ring/15' => ! $errors->has('description'), 'border-destructive focus:border-destructive focus:ring-destructive/15' => $errors->has('description')])>{{ old('description', $project->description) }}</textarea>
                @error('description')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
            </div>

            <div>
                <x-ui.label for="objectives">Objectives</x-ui.label>
                <textarea name="objectives" id="objectives" rows="3" placeholder="One objective per line"
                          @class([$textareaClasses, 'border-input focus:border-ring focus:ring-ring/15' => ! $errors->has('objectives'), 'border-destructive focus:border-destructive focus:ring-destructive/15' => $errors->has('objectives')])>{{ old('objectives', $project->objectives) }}</textarea>
                @error('objectives')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
            </div>

            <div class="grid gap-5 sm:grid-cols-3">
                <div>
                    <x-ui.label for="budget">Budget (ETB)</x-ui.label>
                    <x-ui.input type="number" step="0.01" min="0" id="budget" name="budget" :value="old('budget', $project->budget)" :invalid="$errors->has('budget')" />
                    @error('budget')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
                </div>
                <div>
                    <x-ui.label for="start_date">Start Date</x-ui.label>
                    <x-ui.input type="date" id="start_date" name="start_date" :value="old('start_date', $project->start_date?->format('Y-m-d'))" :invalid="$errors->has('start_date')" />
                    @error('start_date')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
                </div>
                <div>
                    <x-ui.label for="end_date">End Date</x-ui.label>
                    <x-ui.input type="date" id="end_date" name="end_date" :value="old('end_date', $project->end_date?->format('Y-m-d'))" :invalid="$errors->has('end_date')" />
                    @error('end_date')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>
    </x-ui.card>

    <x-ui.card title="Setup" class="self-start">
        <div class="grid gap-5">
            <div>
                <x-ui.label for="template_id">Project Template</x-ui.label>
                <x-ui.select name="template_id" id="template_id" :invalid="$errors->has('template_id')">
                    <option value="">No template (manual setup)</option>
                    @foreach ($templates as $template)
                        <option value="{{ $template->id }}" {{ old('template_id', $project->template_id) == $template->id ? 'selected' : '' }}>
                            {{ $template->name }}
                        </option>
                    @endforeach
                </x-ui.select>
                <p class="m-0 mt-1 text-[13px] text-muted-foreground">Selecting a template will automatically generate phases and tasks.</p>
                @error('template_id')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
            </div>

            @if (isset($project) && $project->exists)
                <div>
                    <x-ui.label for="status">Status</x-ui.label>
                    <x-ui.select name="status" id="status" :invalid="$errors->has('status')">
                        <option value="draft" {{ old('status', $project->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="active" {{ old('status', $project->status) == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="completed" {{ old('status', $project->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="archived" {{ old('status', $project->status) == 'archived' ? 'selected' : '' }}>Archived</option>
                    </x-ui.select>
                    @error('status')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
                </div>
            @endif
        </div>
    </x-ui.card>

    <x-ui.card title="People" description="Who works on this project." class="lg:col-span-3">
        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <x-ui.label for="team_ids">Assign Teams</x-ui.label>
                <select name="team_ids[]" id="team_ids" class="select2 block w-full" multiple>
                    @foreach ($teams as $team)
                        <option value="{{ $team->id }}"
                            {{ in_array($team->id, old('team_ids', $project->teams->pluck('id')->toArray())) ? 'selected' : '' }}>
                            {{ $team->name }}
                        </option>
                    @endforeach
                </select>
                <p class="m-0 mt-1 text-[13px] text-muted-foreground">Select teams that will work on this project.</p>
            </div>
            <div>
                <x-ui.label for="member_ids">Assign Individual Members</x-ui.label>
                <select name="member_ids[]" id="member_ids" class="select2 block w-full" multiple>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}"
                            {{ in_array($user->id, old('member_ids', $project->members->pluck('id')->toArray())) ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
                <p class="m-0 mt-1 text-[13px] text-muted-foreground">Assign specific team members directly to this project.</p>
            </div>
        </div>
    </x-ui.card>
</div>

<div class="mt-6 flex justify-end gap-2">
    @canvisit(route('admin.projects.index'))
        <x-ui.button variant="outline" :href="route('admin.projects.index')">Cancel</x-ui.button>
    @endcanvisit
    <x-ui.button type="submit">
        <i data-lucide="check" class="size-4"></i>
        {{ $submitLabel }}
    </x-ui.button>
</div>

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css" rel="stylesheet">
@endpush

@push('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%'
        });
    });
</script>
@endpush
