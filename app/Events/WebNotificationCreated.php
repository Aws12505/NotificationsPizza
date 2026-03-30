<?php

namespace App\Events;

use App\Models\InAppNotification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WebNotificationCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public InAppNotification $notification
    ) {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('users.' . $this->notification->user_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'notification.created';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->notification->id,
            'user_id' => $this->notification->user_id,
            'type' => $this->notification->type,
            'title' => $this->notification->title,
            'body' => $this->notification->body,
            'action_url' => $this->notification->action_url,
            'data' => $this->notification->data,
            'read_at' => optional($this->notification->read_at)?->toIso8601String(),
            'created_at' => optional($this->notification->created_at)?->toIso8601String(),
        ];
    }
}