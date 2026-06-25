<?php

namespace App\Jobs;

use App\Services\EmailService;
use App\Services\RoleStoreRecipientResolver;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendRoleEmailJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $timeout = 120;

    /**
     * @param  string[]  $roles
     * @param  string[]  $stores
     */
    public function __construct(
        public string $template,
        public string $subject,
        public array $data,
        public array $roles,
        public array $stores,
        public ?bool $includeAllStores = null,
    ) {
    }

    public function handle(EmailService $emailService, RoleStoreRecipientResolver $resolver): void
    {
        $resolver->usersQuery($this->roles, $this->stores, $this->includeAllStores)
            ->select('id', 'email')
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->chunkById(500, function ($users) use ($emailService) {
                foreach ($users as $user) {
                    $emailService->send(
                        template: $this->template,
                        subject: $this->subject,
                        to: $user->email,
                        data: $this->data
                    );
                }
            });
    }
}
