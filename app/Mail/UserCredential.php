<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserCredential extends Mailable
{
    use SerializesModels; //Queueable, 
    protected $name;
    protected $email;
    protected $password;
    protected $company;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($email_data)
    {
        $this->email=$email_data['email'];
        $this->name=$email_data['name'];
        $this->password=$email_data['password'];
        $this->company=$email_data['company'];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Account Credentials')
        ->from('admin@eazytask.au', 'Eazytask')
        ->markdown('emails.user-password')
        ->with(['name' => $notifiable->name,'email' => $notifiable->email,'user_password' => $this->password,'company'=>$this->company]);
    }
}
