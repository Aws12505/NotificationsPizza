<?php

namespace App\Services\EventConsume\Handlers;

use App\Services\EmailService;
use App\Services\EventConsume\EventHandlerInterface;

class EmailSendHandler implements EventHandlerInterface
{
    public function __construct(
        private readonly EmailService $service
    ) {
    }

    public function handle(array $event): void
    {
        $template = (string) data_get($event, 'data.template', '');
        $subject = (string) data_get($event, 'data.subject', '');
        $users = data_get($event, 'data.users', []);

        if ($template === '') {
            throw new \Exception('EmailSendHandler: missing data.template');
        }

        if ($subject === '') {
            throw new \Exception('EmailSendHandler: missing data.subject');
        }

        if (!is_array($users) || empty($users)) {
            throw new \Exception('EmailSendHandler: data.users must be a non-empty array');
        }

        foreach ($users as $index => $user) {
            $to = (string) data_get($user, 'email', '');
            $data = data_get($user, 'data', []);

            if ($to === '') {
                throw new \Exception("EmailSendHandler: users[{$index}].email is required");
            }

            if (!is_array($data)) {
                throw new \Exception("EmailSendHandler: users[{$index}].data must be an array");
            }

            $this->service->send(
                template: $template,
                subject: $subject,
                to: $to,
                data: $data
            );
        }
    }
}