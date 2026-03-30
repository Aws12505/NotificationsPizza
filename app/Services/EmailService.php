<?php

namespace App\Services;

use App\Jobs\SendEmailJob;

class EmailService
{
    public function send(string $template, string $to, array $data): void
    {
        dispatch(new SendEmailJob($template, $to, $data));
    }
}