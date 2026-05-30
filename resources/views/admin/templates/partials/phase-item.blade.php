@php
    $phaseDisplayNumber = is_numeric($phaseIndex) ? ((int) $phaseIndex + 1) : '__PHASE_NUMBER__';
    $phaseSortOrder = $phase['sort_order'] ?? (is_numeric($phaseIndex) ? ((int) $phaseIndex + 1) : 1);
@endphp

<div class="card card-outline card-secondary template-phase-item" data-phase-index="{{ $phaseIndex }}">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Phase <span class="phase-number">{{ $phaseDisplayNumber }}</span></h3>
        <div class="btn-group btn-group-sm">
            <button type="button" class="btn btn-outline-secondary move-phase-up">Up</button>
            <button type="button" class="btn btn-outline-secondary move-phase-down">Down</button>
            <button type="button" class="btn btn-outline-danger remove-phase">Remove Phase</button>
        </div>
    </div>
    <div class="card-body">
        <input type="hidden" name="phases[{{ $phaseIndex }}][sort_order]" value="{{ $phaseSortOrder }}" class="phase-sort-order">

        <div class="form-group">
            <label>Phase Name</label>
            <input type="text" name="phases[{{ $phaseIndex }}][name]" value="{{ $phase['name'] ?? '' }}" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Phase Description</label>
            <textarea name="phases[{{ $phaseIndex }}][description]" rows="2" class="form-control">{{ $phase['description'] ?? '' }}</textarea>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Tasks</h4>
            <button type="button" class="btn btn-sm btn-outline-primary add-task">Add Task</button>
        </div>

        <div class="task-list">
            @foreach ($phase['tasks'] ?? [] as $taskIndex => $task)
                @include('admin.templates.partials.task-item', ['phaseIndex' => $phaseIndex, 'taskIndex' => $taskIndex, 'task' => $task])
            @endforeach
        </div>
    </div>
</div>
