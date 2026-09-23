<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendOtpMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $otp;

    /**
     * Create a new message instance.
     */
    public function __construct(string $otp)
    {
        $this->otp = $otp;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        // The same code serves registration and password reset — keep the
        // subject flow-neutral ("Password Reset" on a signup email reads as
        // phishing and invites the spam folder).
        return $this->subject('Your Shivayra Verification Code')
            ->view('emails.otp-mail');
    }
}
