<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ExistingUserNotification extends Mailable
{
    use SerializesModels; //Queueable,  
    protected $name,$company;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name,$company)
    {
        $this->name=$name;
        $this->company=$company;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Your Eazytask Account')
        ->markdown('emails.existing-user-password')
        ->with([
            'name' => $this->name,
            'company'=>$this->company
        ]);
    }
}
