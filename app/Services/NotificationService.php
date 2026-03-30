<?php

namespace App\Services;

use App\Jobs\SendMobileNotificationJob;
use App\Jobs\SendWebNotificationJob;

class NotificationService
{
    public function send(int $userId, array $channels, array $payload): void
    {
        if (in_array('web', $channels, true)) {
            dispatch(new SendWebNotificationJob($userId, $payload));
        }

        if (in_array('mobile', $channels, true)) {
            dispatch(new SendMobileNotificationJob($userId, $payload));
        }
    }
}