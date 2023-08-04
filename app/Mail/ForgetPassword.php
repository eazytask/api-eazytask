<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ForgetPassword extends Mailable
{
    use Queueable, SerializesModels;
    protected $token,$user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($token,$user)
    {
        $this->token = $token;
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
         $url = 'https://eazytask.au/password/reset/'.$this->token.'?email='.$this->user->email.'';
    
        return $this->subject('Forget-Password Eazytask')
        ->markdown('emails.forget-password')
        ->with([
            'url' => $url,
            'user' => $this->user,
        ]);
    }
}
