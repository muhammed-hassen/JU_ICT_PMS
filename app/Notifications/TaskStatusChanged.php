<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\User;
use Illuminate\Notifications\Notification;

/** In-app notice: a task you created or work on moved to a new status. */
class TaskStatusChanged extends Notification
{
    public function __construct(private Task $task, private string $status, private ?User $by) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'icon' => $this->status === 'Completed' ? 'circle-check' : 'arrow-right-left',
            'title' => $this->task->title.' is now '.$this->status,
            'body' => ($this->by?->name ?? 'Someone').' changed the status.',
            'url' => route('admin.tasks.show', $this->task, false),
            'task_id' => $this->task->id,
        ];
    }
}
