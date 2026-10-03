<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\User;
use Illuminate\Notifications\Notification;

/** In-app notice: someone gave you a task. */
class TaskAssigned extends Notification
{
    public function __construct(private Task $task, private ?User $by) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'icon' => 'user-check',
            'title' => 'New task: '.$this->task->title,
            'body' => ($this->by?->name ?? 'Someone').' assigned this task to you.',
            'url' => route('admin.tasks.show', $this->task, false),
            'task_id' => $this->task->id,
        ];
    }
}
