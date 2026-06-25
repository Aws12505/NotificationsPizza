<?php

namespace App\Services\EventConsume\Handlers;

use App\Jobs\SendRoleEmailJob;
use App\Services\EventConsume\EventHandlerInterface;

class RoleEmailSendHandler implements EventHandlerInterface
{
    public function handle(array $event): void
    {
        $template = (string) data_get($event, 'data.template', '');
        $subject = (string) data_get($event, 'data.subject', '');
        $roles = $this->normalizeList(data_get($event, 'data.roles'));
        $stores = $this->normalizeList(data_get($event, 'data.stores'));
        $data = data_get($event, 'data.data', []);
        $includeAll = data_get($event, 'data.include_all_stores');

        if ($template === '') {
            throw new \Exception('RoleEmailSendHandler: missing data.template');
        }

        if ($subject === '') {
            throw new \Exception('RoleEmailSendHandler: missing data.subject');
        }

        if (empty($roles) && empty($stores)) {
            throw new \Exception('RoleEmailSendHandler: at least one of data.roles or data.stores is required');
        }

        if (!is_array($data)) {
            throw new \Exception('RoleEmailSendHandler: data.data must be an array');
        }

        dispatch(new SendRoleEmailJob(
            template: $template,
            subject: $subject,
            data: $data,
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
