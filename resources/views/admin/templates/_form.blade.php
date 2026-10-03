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

    $textareaClasses = 'block w-full rounded-lg border bg-card px-3 py-2 text-sm text-foreground shadow-xs placeholder:text-muted-foreground/70 focus:outline-none focus:ring-[3px]';
@endphp

<x-ui.card>
    <div class="grid gap-5">
        <div>
            <x-ui.label for="name">Template Name</x-ui.label>
            <x-ui.input
                id="name"
                name="name"
                :value="old('name', $template->name)"
                placeholder="Enter template name"
                :invalid="$errors->has('name')"
                required
            />
            @error('name')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
        </div>

        <div>
            <x-ui.label for="description">Description</x-ui.label>
            <textarea
                name="description"
                id="description"
                rows="2"
                placeholder="Describe this template"
                @class([$textareaClasses, 'border-input focus:border-ring focus:ring-ring/15' => ! $errors->has('description'), 'border-destructive focus:border-destructive focus:ring-destructive/15' => $errors->has('description')])
            >{{ old('description', $template->description) }}</textarea>
            @error('description')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
        </div>

        <label for="is_active" class="m-0 flex cursor-pointer items-center gap-2.5 text-sm font-medium text-foreground">
            <input
                type="checkbox"
                class="size-4 accent-[var(--primary)]"
                id="is_active"
                name="is_active"
                value="1"
                @checked(old('is_active', $template->is_active))
            >
            This template is active and available for use
        </label>
    </div>
</x-ui.card>

@if ($errors->has('phases') || $errors->has('phases.*.name') || $errors->has('phases.*.tasks') || $errors->has('phases.*.tasks.*.title'))
    <div class="mt-5 flex items-start gap-3 rounded-xl border border-destructive/25 bg-destructive/5 px-4 py-3 text-sm text-destructive" role="alert">
        <i data-lucide="circle-alert" class="mt-0.5 size-4 shrink-0"></i>
        <div>
            <p class="m-0 font-semibold">Template structure has validation errors.</p>
            <p class="m-0">Please review the active phase and task details before saving.</p>
        </div>
    </div>
@endif

<div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,2fr)]">
    <div class="self-start rounded-xl border border-border bg-card shadow-xs">
        <div class="flex items-center justify-between gap-3 border-b border-border px-5 py-4">
            <h3 class="m-0 font-sans text-[15px] font-semibold text-foreground">Phases</h3>
            <x-ui.button variant="outline" size="sm" id="add-phase">
                <i data-lucide="plus" class="size-4"></i>
                Add Phase
            </x-ui.button>
        </div>
        <div id="phase-nav" class="flex flex-col"></div>
    </div>

    <div class="rounded-xl border border-border bg-card shadow-xs">
        <div class="flex items-center justify-between gap-3 border-b border-border px-5 py-4">
            <h3 class="m-0 font-sans text-[15px] font-semibold text-foreground">Phase Details</h3>
            <div class="flex items-center gap-0.5">
                <x-ui.icon-button icon="arrow-up" label="Move phase up" id="move-phase-up" class="disabled:pointer-events-none disabled:opacity-40" />
                <x-ui.icon-button icon="arrow-down" label="Move phase down" id="move-phase-down" class="disabled:pointer-events-none disabled:opacity-40" />
                <x-ui.icon-button icon="trash-2" label="Remove phase" tone="destructive" id="remove-phase" class="disabled:pointer-events-none disabled:opacity-40" />
            </div>
        </div>
        <div class="p-5" id="phase-editor"></div>
    </div>
</div>

<div id="structure-inputs"></div>

<div class="mt-6 flex justify-end gap-2">
    @canvisit(route('admin.templates.index'))
        <x-ui.button variant="outline" :href="route('admin.templates.index')">Cancel</x-ui.button>
    @endcanvisit
    <x-ui.button type="submit">
        <i data-lucide="check" class="size-4"></i>
        {{ $submitLabel }}
    </x-ui.button>
</div>

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

        // Same classes as the x-ui.input, x-ui.select, x-ui.label and x-ui.button components.
        const fieldClass = 'block w-full rounded-lg border border-input bg-card px-3 text-sm text-foreground shadow-xs placeholder:text-muted-foreground/70 focus:border-ring focus:outline-none focus:ring-[3px] focus:ring-ring/15';
        const inputClass = `${fieldClass} h-10`;
        const textareaClass = `${fieldClass} py-2`;
        const selectClass = `ui-select ${fieldClass} h-10 appearance-none pr-9`;
        const labelClass = 'mb-1.5 block text-[13px] font-semibold text-foreground';
        const outlineButtonClass = 'inline-flex h-8 items-center justify-center gap-2 whitespace-nowrap rounded-lg border border-input bg-card px-3 text-[13px] font-medium text-foreground shadow-xs transition-colors duration-200 hover:bg-muted disabled:pointer-events-none disabled:opacity-50';
        const primaryButtonClass = 'inline-flex h-8 items-center justify-center gap-2 whitespace-nowrap rounded-lg border-0 bg-primary px-3 text-[13px] font-medium text-primary-foreground shadow-xs transition-colors duration-200 hover:bg-ju-blue-700';
        const iconButtonClass = 'inline-flex size-8 items-center justify-center rounded-lg border-0 bg-transparent text-muted-foreground transition-colors duration-200 hover:bg-muted hover:text-foreground disabled:pointer-events-none disabled:opacity-40';
        const iconButtonDangerClass = 'inline-flex size-8 items-center justify-center rounded-lg border-0 bg-transparent text-muted-foreground transition-colors duration-200 hover:bg-destructive/10 hover:text-destructive disabled:pointer-events-none disabled:opacity-40';

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

        const refreshIcons = () => {
            if (window.lucide) {
                lucide.createIcons();
            }
        };

        const renderPhaseNav = () => {
            phaseNav.innerHTML = phases.map((phase, index) => {
                const isActive = index === activePhaseIndex;
                const phaseName = phase.name.trim() ? phase.name : `Untitled Phase ${index + 1}`;
                const taskLabel = `${phase.tasks.length} task${phase.tasks.length === 1 ? '' : 's'}`;
                const rowClass = isActive
                    ? 'border-l-primary bg-primary/5'
                    : 'border-l-transparent bg-card hover:bg-muted';
                const badgeClass = isActive
                    ? 'border-primary/20 bg-primary/10 text-ju-blue-700'
                    : 'border-border bg-muted text-muted-foreground';

                return `
                    <button type="button" class="flex w-full items-start justify-between gap-3 border-0 border-b border-l-2 border-solid border-b-border px-5 py-3 text-left transition-colors duration-200 last:border-b-0 ${rowClass}" data-phase-select="${index}" ${isActive ? 'aria-current="true"' : ''}>
                        <span class="min-w-0">
                            <span class="block text-sm font-semibold text-foreground">Phase ${index + 1}</span>
                            <span class="mt-0.5 block truncate text-[13px] text-muted-foreground">${escapeHtml(phaseName)}</span>
                        </span>
                        <span class="inline-flex shrink-0 items-center whitespace-nowrap rounded-full border px-2 py-0.5 text-[11px] font-semibold leading-4 ${badgeClass}">${taskLabel}</span>
                    </button>
                `;
            }).join('');
        };

        const renderPhaseEditor = () => {
            const phase = phases[activePhaseIndex];
            const activeTaskIndex = phase.active_task_index ?? 0;
            const task = phase.tasks[activeTaskIndex];

            phaseEditor.innerHTML = `
                <div class="grid gap-5">
                    <div>
                        <label for="active_phase_name" class="${labelClass}">Phase Name</label>
                        <input
                            type="text"
                            id="active_phase_name"
                            class="${inputClass}"
                            placeholder="e.g. Planning, Development, Testing"
                            value="${escapeHtml(phase.name)}"
                            data-phase-field="name"
                        >
                    </div>

                    <div>
                        <label for="active_phase_description" class="${labelClass}">Phase Description</label>
                        <textarea
                            id="active_phase_description"
                            rows="2"
                            class="${textareaClass}"
                            placeholder="Describe what happens in this phase"
                            data-phase-field="description"
                        >${escapeHtml(phase.description)}</textarea>
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-border pt-5">
                    <h4 class="m-0 font-sans text-[15px] font-semibold text-foreground">Tasks</h4>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="task-pagination-summary tabular text-[13px] text-muted-foreground">Task ${activeTaskIndex + 1} of ${phase.tasks.length}</span>
                        <div class="flex items-center gap-1" role="group" aria-label="Task pagination">
                            <button type="button" class="${outlineButtonClass}" id="previous-task" ${activeTaskIndex === 0 ? 'disabled' : ''}>
                                <i data-lucide="chevron-left" class="size-4"></i>
                                Previous Task
                            </button>
                            <button type="button" class="${outlineButtonClass}" id="next-task" ${activeTaskIndex === phase.tasks.length - 1 ? 'disabled' : ''}>
                                Next Task
                                <i data-lucide="chevron-right" class="size-4"></i>
                            </button>
                        </div>
                        <button type="button" class="${primaryButtonClass}" id="add-task">
                            <i data-lucide="plus" class="size-4"></i>
                            Add Task
                        </button>
                    </div>
                </div>

                <div id="task-list" class="mt-4">
                    <div class="rounded-xl border border-border bg-background" data-task-index="${activeTaskIndex}">
                        <div class="flex items-center justify-between gap-3 border-b border-border px-4 py-3">
                            <h5 class="m-0 font-sans text-sm font-semibold text-foreground">Task ${activeTaskIndex + 1}</h5>
                            <div class="flex items-center gap-0.5">
                                <button type="button" class="${iconButtonClass} move-task-up" data-task-move="up" data-task-index="${activeTaskIndex}" title="Move task up" aria-label="Move task up" ${activeTaskIndex === 0 ? 'disabled' : ''}>
                                    <i data-lucide="arrow-up" class="size-4"></i>
                                </button>
                                <button type="button" class="${iconButtonClass} move-task-down" data-task-move="down" data-task-index="${activeTaskIndex}" title="Move task down" aria-label="Move task down" ${activeTaskIndex === phase.tasks.length - 1 ? 'disabled' : ''}>
                                    <i data-lucide="arrow-down" class="size-4"></i>
                                </button>
                                <button type="button" class="${iconButtonDangerClass} remove-task" data-task-remove="${activeTaskIndex}" title="Remove task" aria-label="Remove task" ${phase.tasks.length === 1 ? 'disabled' : ''}>
                                    <i data-lucide="trash-2" class="size-4"></i>
                                </button>
                            </div>
                        </div>
                        <div class="grid gap-4 p-4">
                            <div>
                                <label for="active_task_title" class="${labelClass}">Task Title</label>
                                <input
                                    type="text"
                                    id="active_task_title"
                                    class="${inputClass}"
                                    placeholder="e.g. Design database schema"
                                    value="${escapeHtml(task.title)}"
                                    data-task-field="title"
                                    data-task-index="${activeTaskIndex}"
                                >
                            </div>

                            <div>
                                <label for="active_task_description" class="${labelClass}">Task Description</label>
                                <textarea
                                    id="active_task_description"
                                    rows="2"
                                    class="${textareaClass}"
                                    placeholder="Brief description of the task"
                                    data-task-field="description"
                                    data-task-index="${activeTaskIndex}"
                                >${escapeHtml(task.description)}</textarea>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label for="active_task_priority" class="${labelClass}">Priority</label>
                                    <select id="active_task_priority" class="${selectClass}" data-task-field="task_priority_id" data-task-index="${activeTaskIndex}">
                                        ${taskPriorityOptionsHtml(task.task_priority_id)}
                                    </select>
                                </div>
                                <div>
                                    <label for="active_task_hours" class="${labelClass}">Estimated Hours</label>
                                    <input
                                        type="number"
                                        id="active_task_hours"
                                        min="0"
                                        step="0.5"
                                        class="${inputClass}"
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
            refreshIcons();
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
