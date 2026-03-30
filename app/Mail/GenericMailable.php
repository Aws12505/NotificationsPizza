<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class GenericMailable extends Mailable
{
    public function __construct(
        public string $template,
        public array $data
    ) {
    }

    public function build()
    {
        return $this->view("emails.templates.{$this->template}")
            ->with($this->data);
    }
}