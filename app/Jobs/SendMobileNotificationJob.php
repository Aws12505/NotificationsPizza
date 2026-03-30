<?php

namespace App\Jobs;

use App\Models\UserDevice;
use App\Services\FirebaseService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendMobileNotificationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $userId,
        public array $payload
    ) {
    }

    public function handle(FirebaseService $firebase): void
    {
        $tokens = UserDevice::query()
            ->where('user_id', $this->userId)
            ->pluck('fcm_token')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        foreach ($tokens as $token) {
            $firebase->send($token, $this->payload);
        }
    }
}