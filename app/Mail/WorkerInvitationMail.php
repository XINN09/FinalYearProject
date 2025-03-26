<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WorkerInvitationMail extends Mailable {
    use Queueable, SerializesModels;

    public $invitation;
    public $contractorName;
    public $dailyPay;

    public function __construct($invitation, $contractorName, $dailyPay) {
        $this->invitation = $invitation;
        $this->contractorName = $contractorName;
        $this->dailyPay = $dailyPay;
    }

    public function build() {
        return $this->subject('Invitation to Join Team')
                    ->view('emails.worker_invitation')
                    ->with([
                        'invitation' => $this->invitation,
                        'contractorName' => $this->contractorName,
                        'dailyPay' => $this->dailyPay,
                    ]);
    }
}