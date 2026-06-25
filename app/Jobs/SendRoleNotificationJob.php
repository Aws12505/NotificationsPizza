<?php

namespace App\Jobs;

use App\Services\NotificationService;
use App\Services\RoleStoreRecipientResolver;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendRoleNotificationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $timeout = 120;

    /**
     * @param  string[]  $channels
     * @param  string[]  $roles
     * @param  string[]  $stores
     */
    public function __construct(
        public array $channels,
        public array $payload,
        public array $roles,
        public array $stores,
        public ?bool $includeAllStores = null,
    ) {
    }

    public function handle(NotificationService $notificationService, RoleStoreRecipientResolver $resolver): void
    {
        $resolver->usersQuery($this->roles, $this->stores, $this->includeAllStores)
            ->select('id')
            ->chunkById(500, function ($users) use ($notificationService) {
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
