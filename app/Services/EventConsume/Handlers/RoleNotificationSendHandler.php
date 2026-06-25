<?php

namespace App\Services\EventConsume\Handlers;

use App\Jobs\SendRoleNotificationJob;
use App\Services\EventConsume\EventHandlerInterface;

class RoleNotificationSendHandler implements EventHandlerInterface
{
    public function handle(array $event): void
    {
        $channels = data_get($event, 'data.channels', []);
        $roles = $this->normalizeList(data_get($event, 'data.roles'));
        $stores = $this->normalizeList(data_get($event, 'data.stores'));
        $payload = data_get($event, 'data.payload', []);
        $includeAll = data_get($event, 'data.include_all_stores');

        if (!is_array($channels) || empty($channels)) {
            throw new \Exception('RoleNotificationSendHandler: data.channels must be a non-empty array');
        }

        if (empty($roles) && empty($stores)) {
            throw new \Exception('RoleNotificationSendHandler: at least one of data.roles or data.stores is required');
        }

        if (!is_array($payload)) {
            throw new \Exception('RoleNotificationSendHandler: data.payload must be an array');
        }

        dispatch(new SendRoleNotificationJob(
            channels: $channels,
            payload: $payload,
            roles: $roles,
            stores: $stores,
            includeAllStores: is_bool($includeAll) ? $includeAll : null,
        ));
    }

    /**
     * Accept a string, an array, or null and return a clean list of strings.
     *
     * @return string[]
     */
    private function normalizeList(mixed $value): array
    {
        $items = is_array($value)
            ? $value
            : (($value === null || $value === '') ? [] : [$value]);

        return array_values(array_filter(
            array_map(fn ($v) => trim((string) $v), $items),
            fn ($v) => $v !== ''
        ));
    }
}
