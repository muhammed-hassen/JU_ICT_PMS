<?php

namespace App\Console\Commands;

use App\Models\PhaseMilestone;
use App\Models\Task;
use App\Models\User;
use App\Notifications\DeadlineAlert;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * SRS 5.9: deadline reminders and overdue alerts. Runs daily from the scheduler.
 * Each person gets one alert per item per kind per day, so running it twice
 * changes nothing.
 */
class SendDeadlineAlerts extends Command
{
    protected $signature = 'pms:deadline-alerts';

    protected $description = 'Notify people about tasks and milestones due tomorrow or overdue';

    private int $sent = 0;

    public function handle(): int
    {
        $tomorrow = now()->addDay()->toDateString();
        $today = now()->toDateString();

        $openTasks = Task::query()
            ->whereNotNull('deadline')
            ->whereNull('completed_at')
            ->whereHas('status', fn ($q) => $q->where('name', '!=', 'Completed'))
            ->with(['assignedUsers', 'phase.project.teams']);

        foreach ((clone $openTasks)->whereDate('deadline', $tomorrow)->get() as $task) {
            $this->notifyOnce($this->assigneesOf($task), 'due_soon', "task:{$task->id}",
                "Due tomorrow: {$task->title}",
                'This task is due '.$task->deadline->format('M d, Y').'.',
                route('admin.tasks.show', $task, false));
        }

        foreach ((clone $openTasks)->whereDate('deadline', '<', $today)->get() as $task) {
            $days = (int) $task->deadline->copy()->startOfDay()->diffInDays(now()->startOfDay());
            $this->notifyOnce($this->assigneesOf($task)->merge($this->leadersOf($task->phase?->project)), 'overdue', "task:{$task->id}",
                "Overdue: {$task->title}",
                "This task was due {$task->deadline->format('M d, Y')} ({$days} ".str('day')->plural($days).' ago).',
                route('admin.tasks.show', $task, false));
        }

        $milestones = PhaseMilestone::query()->whereNull('completed_at')->whereNotNull('due_date')
            ->whereDate('due_date', '<=', $tomorrow)->with('phase.project.teams')->get();

        foreach ($milestones as $milestone) {
            $overdue = $milestone->due_date->toDateString() < $today;
            if (! $overdue && $milestone->due_date->toDateString() !== $tomorrow) {
                continue; // due today: yesterday's "due tomorrow" alert already covered it
            }
            $this->notifyOnce($this->leadersOf($milestone->phase?->project), $overdue ? 'overdue' : 'due_soon', "milestone:{$milestone->id}",
                ($overdue ? 'Milestone overdue: ' : 'Milestone due tomorrow: ').$milestone->title,
                "Phase {$milestone->phase->name}, due {$milestone->due_date->format('M d, Y')}.",
                route('admin.phases.show', $milestone->phase, false).'#milestones');
        }

        $this->info("Sent {$this->sent} deadline alerts.");

        return self::SUCCESS;
    }

    private function assigneesOf(Task $task): Collection
    {
        return $task->assignedUsers->pluck('id')->push($task->assigned_to)->filter();
    }

    /** Team leaders of the teams on the project. */
    private function leadersOf($project): Collection
    {
        return $project ? $project->teams->pluck('team_leader_id')->filter() : collect();
    }

    private function notifyOnce(Collection $userIds, string $kind, string $key, string $title, string $body, string $url): void
    {
        foreach (User::whereIn('id', $userIds->unique())->get() as $user) {
            $alreadySent = DB::table('notifications')
                ->where('notifiable_type', $user->getMorphClass())
                ->where('notifiable_id', $user->id)
                ->where('type', DeadlineAlert::class)
                ->whereDate('created_at', now()->toDateString())
                ->where('data', 'like', '%"alert_kind":"'.$kind.'"%')
                ->where('data', 'like', '%"alert_key":"'.$key.'"%')
                ->exists();

            if (! $alreadySent) {
                $user->notify(new DeadlineAlert($kind, $key, $title, $body, $url));
                $this->sent++;
            }
        }
    }
}
