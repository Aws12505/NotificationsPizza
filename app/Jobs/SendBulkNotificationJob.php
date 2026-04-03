<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendBulkNotificationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(
        public array $channels,
        public array $payload,
        public ?array $userIds = null,
        public bool $onlyActiveUsers = true
    ) {
    }

    public function handle(NotificationService $notificationService): void
    {
        $query = User::query()->select('id');

        if ($this->onlyActiveUsers) {
            $query->where('is_active', true);
        }

        if (is_array($this->userIds) && !empty($this->userIds)) {
            $query->whereIn('id', $this->userIds);
        }

        $query->chunkById(100, function ($users) use ($notificationService) {
            foreach ($users as $user) {
                $notificationService->send(
                    userId: $user->id,
                    channels: $this->channels,
                    payload: $this->payload
                );
            }
        });
    }
}