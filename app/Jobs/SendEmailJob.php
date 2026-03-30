<?php

namespace App\Jobs;

use App\Mail\GenericMailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendEmailJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $template,
        public string $to,
        public array $data
    ) {
    }

    public function handle(): void
    {
        Mail::to($this->to)
            ->send(new GenericMailable($this->template, $this->data));
    }
}