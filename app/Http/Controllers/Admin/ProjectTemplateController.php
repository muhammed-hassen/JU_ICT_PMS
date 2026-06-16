<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProjectTemplate;
use App\Models\TaskPriority;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProjectTemplateController extends Controller
{
    public function index(): View
    {
        $templates = ProjectTemplate::query()
            ->withCount('phases')
            ->withCount(['phases as tasks_count' => fn ($query) => $query->join('template_tasks', 'template_tasks.template_phase_id', '=', 'template_phases.id')])
            ->orderBy('name')
            ->paginate(8);

        return view('admin.templates.index', compact('templates'));
    }

    public function create(): View
    {
        return view('admin.templates.create', [
            'template' => new ProjectTemplate(['is_active' => true]),
            'taskPriorities' => TaskPriority::query()->orderBy('level_order')->get(),
            'submitLabel' => 'Create Template',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateTemplate($request);

        DB::transaction(function () use ($request, $validated): void {
            $template = ProjectTemplate::query()->create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'is_active' => (bool) ($validated['is_active'] ?? false),
                'created_by' => $request->user()->id,
                'updated_by' => null,
            ]);

            $this->syncPhases($template, $validated['phases']);
        });

        return redirect()
            ->route('admin.templates.index')
            ->with('status', 'Project template created.');
    }

    public function edit(ProjectTemplate $template): View
    {
        return view('admin.templates.edit', [
            'template' => $template->load(['phases.tasks']),
            'taskPriorities' => TaskPriority::query()->orderBy('level_order')->get(),
            'submitLabel' => 'Update Template',
        ]);
    }

    public function update(Request $request, ProjectTemplate $template): RedirectResponse
    {
        $validated = $this->validateTemplate($request, $template);

        DB::transaction(function () use ($request, $template, $validated): void {
            $template->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'is_active' => (bool) ($validated['is_active'] ?? false),
                'updated_by' => $request->user()->id,
            ]);

            $template->phases()->delete();
            $this->syncPhases($template, $validated['phases']);
        });

        return redirect()
            ->route('admin.templates.index')
            ->with('status', 'Project template updated.');
    }

    public function destroy(ProjectTemplate $template): RedirectResponse
    {
        $template->delete();

        return redirect()
            ->route('admin.templates.index')
            ->with('status', 'Project template deleted.');
    }

    protected function validateTemplate(Request $request, ?ProjectTemplate $template = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('project_templates', 'name')->ignore($template?->id),
            ],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'phases' => ['required', 'array', 'min:1'],
            'phases.*.name' => ['required', 'string', 'max:150'],
            'phases.*.description' => ['nullable', 'string'],
            'phases.*.sort_order' => ['required', 'integer', 'min:1'],
            'phases.*.tasks' => ['required', 'array', 'min:1'],
            'phases.*.tasks.*.title' => ['required', 'string', 'max:200'],
            'phases.*.tasks.*.description' => ['nullable', 'string'],
            'phases.*.tasks.*.sort_order' => ['required', 'integer', 'min:1'],
            'phases.*.tasks.*.task_priority_id' => ['nullable', 'integer', Rule::exists('task_priorities', 'id')],
            'phases.*.tasks.*.estimated_hours' => ['nullable', 'numeric', 'min:0'],
        ]);
    }

    protected function syncPhases(ProjectTemplate $template, array $phases): void
    {
        foreach ($phases as $phaseData) {
            $phase = $template->phases()->create([
                'name' => $phaseData['name'],
                'description' => $phaseData['description'] ?? null,
                'sort_order' => $phaseData['sort_order'],
            ]);

            foreach ($phaseData['tasks'] as $taskData) {
                $phase->tasks()->create([
                    'title' => $taskData['title'],
                    'description' => $taskData['description'] ?? null,
                    'sort_order' => $taskData['sort_order'],
                    'task_priority_id' => $taskData['task_priority_id'] ?? null,
                    'estimated_hours' => $taskData['estimated_hours'] ?? null,
                ]);
            }
        }
    }
}
