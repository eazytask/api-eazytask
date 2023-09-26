<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotifyCredential extends Mailable
{
    use Queueable, SerializesModels;
    protected $name,$email,$password,$company;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($email_data)
    {
        $this->email=$email_data['email'] ?? '';
        $this->name=$email_data['name'] ?? '';
        $this->password=$email_data['password'] ?? '';
        $this->company=$email_data['company'] ?? '';
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Welcome to Eazytask')
        ->markdown('emails.user-password')
        ->with([
            'name' => $this->name,
            'email' => $this->email,
            'user_password' => $this->password,
            'company'=>$this->company
        ]);
    }
}
