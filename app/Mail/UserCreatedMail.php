<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $id_number;
    public $email;
    public $password;

    /**
     * Create a new message instance.
     */
    public function __construct($name, $id_number, $email, $password)
    {
        $this->name = $name;
        $this->id_number = $id_number;
        $this->email = $email;
        $this->password = $password;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('🎉 Your Account Credentials')
            ->view('emails.user-created');
    }
}
