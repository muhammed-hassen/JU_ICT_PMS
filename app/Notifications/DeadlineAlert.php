<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

/** In-app alert: a task or milestone is due tomorrow, or already overdue. */
class DeadlineAlert extends Notification
{
    /**
     * @param  string  $kind  due_soon or overdue
     * @param  string  $key  what the alert is about, e.g. task:12 or milestone:4 (used to avoid repeats)
     */
    public function __construct(
        private string $kind,
        private string $key,
        private string $title,
        private string $body,
        private string $url,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'icon' => $this->kind === 'overdue' ? 'alarm-clock-off' : 'alarm-clock',
            'title' => $this->title,
            'body' => $this->body,
            'url' => $this->url,
            'alert_kind' => $this->kind,
            'alert_key' => $this->key,
        ];
    }
}
