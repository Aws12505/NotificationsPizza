<?php

namespace App\Jobs;

use App\Events\WebNotificationCreated;
use App\Models\InAppNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendWebNotificationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $userId,
        public array $payload
    ) {
    }

    public function handle(): void
    {
        $notification = InAppNotification::query()->create([
            'user_id' => $this->userId,
            'type' => data_get($this->payload, 'type'),
            'title' => (string) data_get($this->payload, 'title', ''),
            'body' => data_get($this->payload, 'body'),
            'action_url' => data_get($this->payload, 'action_url'),
            'data' => $this->payload,
        ]);

        broadcast(new WebNotificationCreated($notification))->toOthers();
    }
}