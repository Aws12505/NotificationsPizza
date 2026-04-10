<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

Broadcast::channel('users.{userId}', function ($user, $userId) {
    Log::info('channel callback hit', [
        'auth_user_id' => $user?->id,
        'requested_user_id' => $userId,
    ]);

    return (int) $user->id === (int) $userId;
});