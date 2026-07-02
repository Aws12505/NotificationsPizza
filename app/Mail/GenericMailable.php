<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class GenericMailable extends Mailable
{
    public function __construct(
        public string $template,
        public string $subjectLine,
        public array $data
    ) {
    }

    public function build()
    {
        return $this->subject($this->subjectLine)
            ->view("emails.templates.{$this->template}")
            ->with($this->data);
    }
}