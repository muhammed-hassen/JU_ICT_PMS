@php
    $oldPhases = old('phases');
    $phases = $oldPhases ?? $template->phases->map(function ($phase) {
        return [
            'name' => $phase->name,
            'description' => $phase->description,
            'sort_order' => $phase->sort_order,
            'tasks' => $phase->tasks->map(function ($task) {
                return [
                    'title' => $task->title,
                    'description' => $task->description,
                    'task_priority_id' => $task->task_priority_id,
                    'estimated_hours' => $task->estimated_hours,
                    'sort_order' => $task->sort_order,
                ];
            })->values()->all(),
        ];
    })->values()->all();

    if (empty($phases)) {
        $phases = [[
            'name' => '',
            'description' => '',
            'sort_order' => 1,
            'tasks' => [[
                'title' => '',
                'description' => '',
                'task_priority_id' => null,
                'estimated_hours' => '',
                'sort_order' => 1,
            ]],
        ]];
    }

    $priorityOptions = $taskPriorities->map(fn ($priority) => [
        'id' => $priority->id,
        'name' => $priority->name,
    ])->values()->all();
@endphp

<div class="card-body p-0">
    <div class="px-4 py-4 border-bottom bg-light">
        <div class="form-group mb-0">
            <label for="name" class="form-label fw-600">Template Name</label>
            <input
                type="text"
                name="name"
                id="name"
                value="{{ old('name', $template->name) }}"
                class="form-control form-control-lg @error('name') is-invalid @enderror"
                placeholder="Enter template name"
                required
            >
            @error('name')
                <span class="invalid-feedback d-block">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="px-4 py-4 border-bottom">
        <div class="form-group mb-0">
            <label for="description" class="form-label fw-600">Description</label>
            <textarea
                name="description"
                id="description"
                rows="2"
                class="form-control @error('description') is-invalid @enderror"
                placeholder="Describe this template..."
            >{{ old('description', $template->description) }}</textarea>
            @error('description')
                <span class="invalid-feedback d-block">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="px-4 py-4 border-bottom">
        <div class="custom-control custom-switch template-switch">
            <input
                type="checkbox"
                class="custom-control-input"
                id="is_active"
                name="is_active"
                value="1"
                @checked(old('is_active', $template->is_active))
            >
            <label class="custom-control-label fw-500" for="is_active">
                This template is active and available for use
            </label>
        </div>
    </div>

    @if ($errors->has('phases') || $errors->has('phases.*.name') || $errors->has('phases.*.tasks') || $errors->has('phases.*.tasks.*.title'))
        <div class="alert alert-danger mx-4 my-3 mb-0 border-0 template-structure-alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <div>
                    <strong>Template structure has validation errors.</strong>
                    <div class="small">Please review the active phase and task details before saving.</div>
                </div>
            </div>
        </div>
    @endif
</div>

<div class="card-body px-0 py-4">
    <div class="row mx-0">
        <div class="col-lg-4 mb-3 mb-lg-0">
            <div class="card card-outline card-primary border-0 shadow-sm h-100 mb-0">
                <div class="card-header bg-primary-gradient d-flex justify-content-between align-items-center border-0">
                    <h5 class="card-title mb-0 text-white fw-600">Phases</h5>
                    <button
                        type="button"
                        class="btn btn-sm btn-light text-primary fw-600"
                        id="add-phase"
                        title="Add new phase"
                    >
                        <i class="fas fa-plus mr-1"></i>
                        Add Phase
                    </button>
                </div>
                <div class="card-body p-0">
                    <div id="phase-nav" class="list-group list-group-flush rounded-0"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card card-outline card-secondary border-0 shadow-sm mb-0">
                <div class="card-header bg-secondary-gradient d-flex justify-content-between align-items-center border-0">
                    <h5 class="card-title mb-0 text-white fw-600">Phase Details</h5>
                    <div class="btn-group btn-group-sm ml-auto" role="group">
                        <button type="button" class="btn btn-outline-light btn-sm fw-500" id="move-phase-up" title="Move phase up">
                            <i class="fas fa-arrow-up"></i>
                        </button>
                        <button type="button" class="btn btn-outline-light btn-sm fw-500" id="move-phase-down" title="Move phase down">
                            <i class="fas fa-arrow-down"></i>
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm fw-500" id="remove-phase" title="Remove phase">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body" id="phase-editor"></div>
            </div>
        </div>
    </div>

    <div id="structure-inputs"></div>
</div>

<div class="card-footer bg-light border-top d-flex justify-content-end template-footer-actions">
    <a href="{{ route('admin.templates.index') }}" class="btn btn-light border fw-600">Cancel</a>
    <button type="submit" class="btn btn-primary template-blue-variant fw-600 px-4">{{ $submitLabel }}</button>
</div>

@push('css')
<style>
    .card-elevated {
        border: none;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .bg-primary-gradient {
        background: linear-gradient(135deg, #1f75cb 0%, #3b5bd6 100%);
    }

    .bg-secondary-gradient {
        background: linear-gradient(135deg, #6c757d 0%, #7a8288 100%);
    }

    .form-label {
        font-size: 0.875rem;
        color: #2c3e50;
        margin-bottom: 0.5rem;
    }

    .form-control-lg {
        padding: 0.75rem 1rem;
        font-size: 1rem;
        border: 1px solid #e0e0e0;
        border-radius: 0.375rem;
        transition: all 0.2s ease;
    }

    .form-control-lg:focus,
    .form-control:focus {
        border-color: #1f75cb;
        box-shadow: 0 0 0 3px rgba(31, 117, 203, 0.1);
    }

    .fw-600 {
        font-weight: 600;
    }

    .fw-500 {
        font-weight: 500;
    }

    .template-blue-variant {
        background: linear-gradient(135deg, var(--auth-button, #1f75cb) 0%, #3b5bd6 100%) !important;
        border-color: transparent !important;
        color: var(--auth-button-text, #ffffff) !important;
        box-shadow: 0 10px 22px rgba(31, 117, 203, 0.2);
        --auth-button: #1f75cb;
        --auth-button-text: #ffffff;
    }

    .template-blue-variant:hover,
    .template-blue-variant:focus,
    .template-blue-variant:active {
        background: linear-gradient(135deg, #1068bf 0%, #314fc7 100%) !important;
        border-color: transparent !important;
        color: #ffffff !important;
        box-shadow: 0 14px 28px rgba(31, 117, 203, 0.24);
    }

    #phase-nav .list-group-item {
        border: none;
        border-bottom: 1px solid #e0e0e0;
        padding: 1rem;
        transition: all 0.2s ease;
    }

    #phase-nav .list-group-item:hover {
        background-color: #f8f9fa;
    }

    #phase-nav .list-group-item.active {
        background-color: #e7f0f7;
        border-left: 3px solid #1f75cb;
        padding-left: calc(1rem - 3px);
    }

    .template-structure-alert {
        background-color: #fff5f5;
        border-left: 4px solid #dc3545 !important;
        color: #721c24;
        border-radius: 0.5rem;
    }

    .template-footer-actions {
        gap: 0.5rem;
    }

    .template-switch .custom-control-label {
        padding-top: 0.1rem;
    }

    .task-pagination-controls {
        gap: 0.5rem;
    }

    .task-pagination-summary {
        font-size: 0.875rem;
        color: #6c757d;
    }

    .btn-light {
        border-color: #d9d9d9;
    }

    .btn-light:hover {
        background-color: #f8f9fa;
        border-color: #c0c0c0;
    }

    .btn-outline-light {
        color: #fff;
        border-color: rgba(255, 255, 255, 0.5);
    }

    .btn-outline-light:hover,
    .btn-outline-light:focus {
        background-color: rgba(255, 255, 255, 0.1);
        border-color: #fff;
        color: #fff;
    }
</style>
@endpush

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('form[action*="templates"]');
        const phaseNav = document.getElementById('phase-nav');
        const phaseEditor = document.getElementById('phase-editor');
        const addPhaseButton = document.getElementById('add-phase');
        const removePhaseButton = document.getElementById('remove-phase');
        const movePhaseUpButton = document.getElementById('move-phase-up');
        const movePhaseDownButton = document.getElementById('move-phase-down');
        const structureInputs = document.getElementById('structure-inputs');
        const priorityOptions = @json($priorityOptions);

        let phases = @json($phases);
        let activePhaseIndex = phases.length ? 0 : null;

        const createDefaultTask = () => ({
            title: '',
            description: '',
            task_priority_id: '',
            estimated_hours: '',
            sort_order: 1,
        });

        const createDefaultPhase = (phaseNumber) => ({
            name: '',
            description: '',
            sort_order: phaseNumber,
            tasks: [createDefaultTask()],
            active_task_index: 0,
        });

        const normalizeStructure = () => {
            phases = phases.map((phase, phaseIndex) => ({
                name: phase.name ?? '',
                description: phase.description ?? '',
                sort_order: phaseIndex + 1,
                tasks: (phase.tasks && phase.tasks.length ? phase.tasks : [createDefaultTask()]).map((task, taskIndex) => ({
                    title: task.title ?? '',
                    description: task.description ?? '',
                    task_priority_id: task.task_priority_id ?? '',
                    estimated_hours: task.estimated_hours ?? '',
                    sort_order: taskIndex + 1,
                })),
                active_task_index: Math.min(
                    Math.max(Number(phase.active_task_index ?? 0), 0),
                    Math.max((phase.tasks && phase.tasks.length ? phase.tasks.length : 1) - 1, 0)
                ),
            }));

            if (!phases.length) {
                phases = [createDefaultPhase(1)];
                activePhaseIndex = 0;
            }

            if (activePhaseIndex === null || activePhaseIndex >= phases.length) {
                activePhaseIndex = 0;
            }
        };

        const escapeHtml = (value) => {
            const div = document.createElement('div');
            div.textContent = value ?? '';
            return div.innerHTML;
        };

        const taskPriorityOptionsHtml = (selectedValue) => {
            const current = selectedValue === null || selectedValue === undefined ? '' : String(selectedValue);
            const options = ['<option value="">Select priority</option>'];

            priorityOptions.forEach((priority) => {
                const selected = String(priority.id) === current ? ' selected' : '';
                options.push(`<option value="${priority.id}"${selected}>${escapeHtml(priority.name)}</option>`);
            });

            return options.join('');
        };

        const renderPhaseNav = () => {
            phaseNav.innerHTML = phases.map((phase, index) => {
                const activeClass = index === activePhaseIndex ? 'active' : '';
                const phaseName = phase.name.trim() ? phase.name : `Untitled Phase ${index + 1}`;
                const taskLabel = `${phase.tasks.length} task${phase.tasks.length === 1 ? '' : 's'}`;
                const badgeClass = index === activePhaseIndex ? 'badge-light text-primary' : 'badge-secondary';

                return `
                    <button type="button" class="list-group-item list-group-item-action text-left ${activeClass}" data-phase-select="${index}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="fw-600 text-dark" style="font-size: 0.95rem;">Phase ${index + 1}</div>
                                <div class="text-muted small mt-1" style="font-size: 0.875rem;">${escapeHtml(phaseName)}</div>
                            </div>
                            <span class="badge ${badgeClass}" style="white-space: nowrap;">${taskLabel}</span>
                        </div>
                    </button>
                `;
            }).join('');
        };

        const renderPhaseEditor = () => {
            const phase = phases[activePhaseIndex];
            const activeTaskIndex = phase.active_task_index ?? 0;
            const task = phase.tasks[activeTaskIndex];

            phaseEditor.innerHTML = `
                <div class="form-group mb-4">
                    <label for="active_phase_name" class="form-label fw-600">Phase Name</label>
                    <input
                        type="text"
                        id="active_phase_name"
                        class="form-control"
                        placeholder="e.g., Planning, Development, Testing"
                        value="${escapeHtml(phase.name)}"
                        data-phase-field="name"
                    >
                </div>

                <div class="form-group mb-4">
                    <label for="active_phase_description" class="form-label fw-600">Phase Description</label>
                    <textarea
                        id="active_phase_description"
                        rows="2"
                        class="form-control"
                        placeholder="Describe what happens in this phase..."
                        data-phase-field="description"
                    >${escapeHtml(phase.description)}</textarea>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="mb-0 fw-600 text-dark">Tasks</h6>
                    <div class="d-flex align-items-center task-pagination-controls">
                        <span class="task-pagination-summary">Task ${activeTaskIndex + 1} of ${phase.tasks.length}</span>
                        <div class="btn-group btn-group-sm" role="group" aria-label="Task pagination">
                            <button type="button" class="btn btn-outline-secondary" id="previous-task" ${activeTaskIndex === 0 ? 'disabled' : ''}>Previous Task</button>
                            <button type="button" class="btn btn-outline-secondary" id="next-task" ${activeTaskIndex === phase.tasks.length - 1 ? 'disabled' : ''}>Next Task</button>
                        </div>
                        <button type="button" class="btn btn-primary btn-sm template-blue-variant" id="add-task">Add Task</button>
                    </div>
                </div>

                <div id="task-list">
                    <div class="card border-0 mb-3 shadow-sm" data-task-index="${activeTaskIndex}" style="background-color: #f8f9fa;">
                        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                            <h6 class="card-title mb-0 fw-600 text-dark">Task ${activeTaskIndex + 1}</h6>
                            <div class="btn-group btn-group-sm ml-auto">
                                <button type="button" class="btn btn-outline-secondary btn-sm move-task-up" data-task-move="up" data-task-index="${activeTaskIndex}" title="Move task up" ${activeTaskIndex === 0 ? 'disabled' : ''}>
                                    <i class="fas fa-arrow-up"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm move-task-down" data-task-move="down" data-task-index="${activeTaskIndex}" title="Move task down" ${activeTaskIndex === phase.tasks.length - 1 ? 'disabled' : ''}>
                                    <i class="fas fa-arrow-down"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm remove-task" data-task-remove="${activeTaskIndex}" title="Remove task" ${phase.tasks.length === 1 ? 'disabled' : ''}>
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-3">
                                <label class="form-label fw-600 small">Task Title</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    placeholder="e.g., Design database schema"
                                    value="${escapeHtml(task.title)}"
                                    data-task-field="title"
                                    data-task-index="${activeTaskIndex}"
                                >
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label fw-600 small">Task Description</label>
                                <textarea
                                    rows="2"
                                    class="form-control"
                                    placeholder="Brief description of the task..."
                                    data-task-field="description"
                                    data-task-index="${activeTaskIndex}"
                                >${escapeHtml(task.description)}</textarea>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6 mb-3">
                                    <label class="form-label fw-600 small">Priority</label>
                                    <select class="form-control" data-task-field="task_priority_id" data-task-index="${activeTaskIndex}">
                                        ${taskPriorityOptionsHtml(task.task_priority_id)}
                                    </select>
                                </div>
                                <div class="form-group col-md-6 mb-3">
                                    <label class="form-label fw-600 small">Estimated Hours</label>
                                    <input
                                        type="number"
                                        min="0"
                                        step="0.5"
                                        class="form-control"
                                        placeholder="0.0"
                                        value="${escapeHtml(task.estimated_hours)}"
                                        data-task-field="estimated_hours"
                                        data-task-index="${activeTaskIndex}"
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            removePhaseButton.disabled = phases.length === 1;
            movePhaseUpButton.disabled = activePhaseIndex === 0;
            movePhaseDownButton.disabled = activePhaseIndex === phases.length - 1;
        };

        const render = () => {
            normalizeStructure();
            renderPhaseNav();
            renderPhaseEditor();
        };

        const moveItem = (items, index, direction) => {
            const targetIndex = direction === 'up' ? index - 1 : index + 1;

            if (targetIndex < 0 || targetIndex >= items.length) {
                return index;
            }

            [items[index], items[targetIndex]] = [items[targetIndex], items[index]];

            return targetIndex;
        };

        const appendHiddenInput = (name, value) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value ?? '';
            structureInputs.appendChild(input);
        };

        phaseNav.addEventListener('click', function (event) {
            const button = event.target.closest('[data-phase-select]');

            if (!button) {
                return;
            }

            activePhaseIndex = Number(button.dataset.phaseSelect);
            render();
        });

        phaseEditor.addEventListener('input', function (event) {
            const phaseField = event.target.dataset.phaseField;

            if (phaseField) {
                phases[activePhaseIndex][phaseField] = event.target.value;
                renderPhaseNav();
            }

            const taskField = event.target.dataset.taskField;

            if (taskField) {
                const taskIndex = Number(event.target.dataset.taskIndex);
                phases[activePhaseIndex].tasks[taskIndex][taskField] = event.target.value;
            }
        });

        phaseEditor.addEventListener('change', function (event) {
            const taskField = event.target.dataset.taskField;

            if (!taskField) {
                return;
            }

            const taskIndex = Number(event.target.dataset.taskIndex);
            phases[activePhaseIndex].tasks[taskIndex][taskField] = event.target.value;
        });

        phaseEditor.addEventListener('click', function (event) {
            const addTaskButton = event.target.closest('#add-task');

            if (addTaskButton) {
                phases[activePhaseIndex].tasks.push(createDefaultTask());
                phases[activePhaseIndex].active_task_index = phases[activePhaseIndex].tasks.length - 1;
                render();
                return;
            }

            const previousTaskButton = event.target.closest('#previous-task');

            if (previousTaskButton && !previousTaskButton.disabled) {
                phases[activePhaseIndex].active_task_index -= 1;
                render();
                return;
            }

            const nextTaskButton = event.target.closest('#next-task');

            if (nextTaskButton && !nextTaskButton.disabled) {
                phases[activePhaseIndex].active_task_index += 1;
                render();
                return;
            }

            const removeTaskButton = event.target.closest('[data-task-remove]');
            const removeTaskIndex = removeTaskButton ? removeTaskButton.dataset.taskRemove : undefined;

            if (removeTaskIndex !== undefined) {
                phases[activePhaseIndex].tasks.splice(Number(removeTaskIndex), 1);
                phases[activePhaseIndex].active_task_index = Math.min(
                    Number(phases[activePhaseIndex].active_task_index ?? 0),
                    phases[activePhaseIndex].tasks.length - 1
                );
                render();
                return;
            }

            const taskMoveButton = event.target.closest('[data-task-move]');
            const taskDirection = taskMoveButton ? taskMoveButton.dataset.taskMove : undefined;

            if (taskDirection) {
                phases[activePhaseIndex].active_task_index = moveItem(
                    phases[activePhaseIndex].tasks,
                    Number(taskMoveButton.dataset.taskIndex),
                    taskDirection
                );
                render();
            }
        });

        addPhaseButton.addEventListener('click', function () {
            phases.push(createDefaultPhase(phases.length + 1));
            activePhaseIndex = phases.length - 1;
            render();
        });

        removePhaseButton.addEventListener('click', function () {
            if (phases.length === 1) {
                return;
            }

            phases.splice(activePhaseIndex, 1);

            if (activePhaseIndex >= phases.length) {
                activePhaseIndex = phases.length - 1;
            }

            render();
        });

        movePhaseUpButton.addEventListener('click', function () {
            activePhaseIndex = moveItem(phases, activePhaseIndex, 'up');
            render();
        });

        movePhaseDownButton.addEventListener('click', function () {
            activePhaseIndex = moveItem(phases, activePhaseIndex, 'down');
            render();
        });

        form.addEventListener('submit', function () {
            normalizeStructure();
            structureInputs.innerHTML = '';

            phases.forEach((phase, phaseIndex) => {
                appendHiddenInput(`phases[${phaseIndex}][name]`, phase.name);
                appendHiddenInput(`phases[${phaseIndex}][description]`, phase.description);
                appendHiddenInput(`phases[${phaseIndex}][sort_order]`, phaseIndex + 1);

                phase.tasks.forEach((task, taskIndex) => {
                    appendHiddenInput(`phases[${phaseIndex}][tasks][${taskIndex}][title]`, task.title);
                    appendHiddenInput(`phases[${phaseIndex}][tasks][${taskIndex}][description]`, task.description);
                    appendHiddenInput(`phases[${phaseIndex}][tasks][${taskIndex}][task_priority_id]`, task.task_priority_id);
                    appendHiddenInput(`phases[${phaseIndex}][tasks][${taskIndex}][estimated_hours]`, task.estimated_hours);
                    appendHiddenInput(`phases[${phaseIndex}][tasks][${taskIndex}][sort_order]`, taskIndex + 1);
                });
            });
        });

        render();
    });
</script>
@endpush
