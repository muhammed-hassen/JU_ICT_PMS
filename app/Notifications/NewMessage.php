<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

/** In-app notice: someone sent you a message. */
class NewMessage extends Notification
{
    public function __construct(private Message $message) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $conversation = $this->message->conversation;

        return [
            'icon' => 'message-square',
            'title' => 'Message from '.($this->message->sender?->name ?? 'someone'),
            'body' => Str::limit($this->message->body, 80),
            'url' => route('messages.show', $conversation, false),
            'conversation_id' => $conversation->id,
        ];
    }
}
