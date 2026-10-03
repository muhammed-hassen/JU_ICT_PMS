<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Phase;
use App\Models\PhaseMilestone;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** A phase's milestones: deadline plus deliverable. Whoever can edit the phase manages them. */
class PhaseMilestoneController extends Controller
{
    public function __construct(private ActivityLogService $activityLog) {}

    public function store(Request $request, Phase $phase): RedirectResponse
    {
        $this->authorizePhase($phase);

        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'deliverable' => 'nullable|string|max:255',
            'due_date' => 'nullable|date',
        ]);

        $milestone = $phase->milestones()->create($validated + ['created_by' => $request->user()->id]);

        $this->activityLog->log($phase, 'updated', "Milestone added: {$milestone->title}", ['milestone_id' => $milestone->id]);

        return back()->with('success', 'Milestone added.');
    }

    public function toggle(PhaseMilestone $milestone): RedirectResponse
    {
        $this->authorizePhase($milestone->phase);

        $milestone->update(['completed_at' => $milestone->completed_at ? null : now()]);

        $this->activityLog->log($milestone->phase, 'updated',
            ($milestone->completed_at ? 'Milestone reached: ' : 'Milestone reopened: ').$milestone->title);

        return back();
    }

    public function destroy(PhaseMilestone $milestone): RedirectResponse
    {
        $phase = $milestone->phase;
        $this->authorizePhase($phase);

        $milestone->delete();
        $this->activityLog->log($phase, 'updated', "Milestone removed: {$milestone->title}");

        return back()->with('success', 'Milestone removed.');
    }

    private function authorizePhase(Phase $phase): void
    {
        $user = auth()->user();

        abort_unless($user->isDirector() || in_array($phase->project_id, $user->getVisibleProjectIds()), 403,
            'You do not have permission to change this phase.');
    }
}
