@php
    $taskDisplayNumber = is_numeric($taskIndex) ? ((int) $taskIndex + 1) : '__TASK_NUMBER__';
    $taskSortOrder = $task['sort_order'] ?? (is_numeric($taskIndex) ? ((int) $taskIndex + 1) : 1);
@endphp

<div class="border rounded p-3 mb-3 template-task-item" data-task-index="{{ $taskIndex }}">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <strong>Task <span class="task-number">{{ $taskDisplayNumber }}</span></strong>
        <div class="btn-group btn-group-sm">
            <button type="button" class="btn btn-outline-secondary move-task-up">Up</button>
            <button type="button" class="btn btn-outline-secondary move-task-down">Down</button>
            <button type="button" class="btn btn-outline-danger remove-task">Remove Task</button>
        </div>
    </div>

    <input type="hidden" name="phases[{{ $phaseIndex }}][tasks][{{ $taskIndex }}][sort_order]" value="{{ $taskSortOrder }}" class="task-sort-order">

    <div class="form-group">
        <label>Task Title</label>
        <input type="text" name="phases[{{ $phaseIndex }}][tasks][{{ $taskIndex }}][title]" value="{{ $task['title'] ?? '' }}" class="form-control" required>
    </div>

    <div class="form-group">
        <label>Task Description</label>
        <textarea name="phases[{{ $phaseIndex }}][tasks][{{ $taskIndex }}][description]" rows="2" class="form-control">{{ $task['description'] ?? '' }}</textarea>
    </div>

    <div class="form-row">
        <div class="form-group col-md-6">
            <label>Priority</label>
            <select name="phases[{{ $phaseIndex }}][tasks][{{ $taskIndex }}][task_priority_id]" class="form-control">
                <option value="">Select priority</option>
                @foreach ($taskPriorities as $priority)
                    <option value="{{ $priority->id }}" @selected(($task['task_priority_id'] ?? null) == $priority->id)>{{ $priority->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-md-6">
            <label>Estimated Hours</label>
            <input type="number" step="0.01" min="0" name="phases[{{ $phaseIndex }}][tasks][{{ $taskIndex }}][estimated_hours]" value="{{ $task['estimated_hours'] ?? '' }}" class="form-control">
        </div>
    </div>
</div>
