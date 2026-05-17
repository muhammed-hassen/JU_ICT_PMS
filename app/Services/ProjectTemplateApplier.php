<?php

namespace App\Services;

use App\Models\Phase;
use App\Models\PhaseStatus;
use App\Models\Project;
use App\Models\ProjectTemplate;
use App\Models\Task;
use App\Models\TaskStatus;
use Illuminate\Support\Facades\DB;

class ProjectTemplateApplier
{
    public function apply(int $projectId, int $templateId, int $actorId): void
    {
        $project = Project::query()->findOrFail($projectId);
        $template = ProjectTemplate::query()
            ->with(['phases.tasks'])
            ->findOrFail($templateId);

        $defaultPhaseStatusId = (int) PhaseStatus::query()
            ->where('name', 'Not Started')
            ->value('id');

        $defaultTaskStatusId = (int) TaskStatus::query()
            ->where('name', 'Not Started')
            ->value('id');

        DB::transaction(function () use ($actorId, $defaultPhaseStatusId, $defaultTaskStatusId, $project, $template): void {
            $phaseMap = [];

            foreach ($template->phases as $templatePhase) {
                $phase = Phase::query()->create([
                    'project_id' => $project->id,
                    'phase_status_id' => $defaultPhaseStatusId,
                    'name' => $templatePhase->name,
                    'description' => $templatePhase->description,
                    'sort_order' => $templatePhase->sort_order,
                    'progress_percentage' => 0,
                    'created_by' => $actorId,
                    'updated_by' => null,
                ]);

                $phaseMap[$templatePhase->id] = $phase->id;
            }

            foreach ($template->phases as $templatePhase) {
                foreach ($templatePhase->tasks as $templateTask) {
                    Task::query()->create([
                        'phase_id' => $phaseMap[$templateTask->template_phase_id],
                        'task_status_id' => $defaultTaskStatusId,
                        'task_priority_id' => $templateTask->task_priority_id,
                        'assigned_to' => null,
                        'title' => $templateTask->title,
                        'description' => $templateTask->description,
                        'estimated_hours' => $templateTask->estimated_hours,
                        'progress_percentage' => 0,
                        'created_by' => $actorId,
                        'updated_by' => null,
                    ]);
                }
            }
        });
    }
}
