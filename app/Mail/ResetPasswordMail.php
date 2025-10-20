<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $email;
    public $url;

    /**
     * Create a new message instance.
     */
    public function __construct($name, $email, $url)
    {
        $this->name  = $name;
        $this->email = $email;
        $this->url   = $url;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Reset Your Password')
            ->view('emails.reset-password');
    }
}
