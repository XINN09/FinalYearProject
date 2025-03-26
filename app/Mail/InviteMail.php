<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public $acceptUrl;

    public function __construct($acceptUrl)
    {
        $this->acceptUrl = $acceptUrl;
    }

    public function build()
    {
        return $this->subject('Project Invitation')
            ->view('emails.invite')
            ->with([
                'acceptUrl' => $this->acceptUrl,
            ]);
    }
}