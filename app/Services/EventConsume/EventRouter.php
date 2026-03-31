<?php

namespace App\Services\EventConsume;

use App\Services\EventConsume\Handlers\EmailSendHandler;
use App\Services\EventConsume\Handlers\NotificationSendHandler;
use App\Services\EventConsume\Handlers\UserCreatedHandler;
use App\Services\EventConsume\Handlers\UserDeletedHandler;
use App\Services\EventConsume\Handlers\UserDeviceUpsertedHandler;
use App\Services\EventConsume\Handlers\UserUpdatedHandler;
use Exception;

class EventRouter
{
    /** @var array<string, class-string<EventHandlerInterface>> */
    private array $map;

    public function __construct()
    {
        $devMode = (bool) config('nats.dev_mode');

        $authPrefix = $devMode
            ? 'auth.testing.v1'
            : 'auth.v1';

        $notificationsPrefix = $devMode
            ? 'notifications.testing.v1'
            : 'notifications.v1';

        $this->map = [

            // USERS
            "{$authPrefix}.user.created" => UserCreatedHandler::class,
            "{$authPrefix}.user.updated" => UserUpdatedHandler::class,
            "{$authPrefix}.user.deleted" => UserDeletedHandler::class,

            // DEVICES
            "{$authPrefix}.user.device.upserted" => UserDeviceUpsertedHandler::class,

            // COMMUNICATION
            "{$notificationsPrefix}.email.send" => EmailSendHandler::class,
            "{$notificationsPrefix}.notification.send" => NotificationSendHandler::class,
        ];
    }
    public function getResolvedMap(): array
    {
        return $this->map;
    }
    public function resolve(string $subject): string
    {
        if (!isset($this->map[$subject])) {
            throw new Exception("No handler for subject '{$subject}'");
        }

        return $this->map[$subject];
    }
}