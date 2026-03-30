<?php

namespace App\Services\EventConsume\Handlers;

use App\Services\NotificationService;
use App\Services\EventConsume\EventHandlerInterface;

class NotificationSendHandler implements EventHandlerInterface
{
    public function __construct(
        private readonly NotificationService $service
    ) {
    }

    public function handle(array $event): void
    {
        $channels = data_get($event, 'data.channels', []);
        $users = data_get($event, 'data.users', []);

        if (!is_array($channels) || empty($channels)) {
            throw new \Exception('NotificationSendHandler: data.channels must be a non-empty array');
        }

        if (!is_array($users) || empty($users)) {
            throw new \Exception('NotificationSendHandler: data.users must be a non-empty array');
        }

        foreach ($users as $index => $user) {
            $userId = $this->asInt(data_get($user, 'id'));
            $payload = data_get($user, 'data', []);

            if ($userId <= 0) {
                throw new \Exception("NotificationSendHandler: users[{$index}].id is required");
            }

            if (!is_array($payload)) {
                throw new \Exception("NotificationSendHandler: users[{$index}].data must be an array");
            }

            $this->service->send(
                userId: $userId,
                channels: $channels,
                payload: $payload
            );
        }
    }

    private function asInt(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && ctype_digit($value)) {
            return (int) $value;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        return 0;
    }
}