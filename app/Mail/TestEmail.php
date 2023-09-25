<?php 

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TestEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function build()
    {
        return $this
            ->subject('Test Email Subject')
            ->markdown('emails.test'); // Use the 'markdown' method to define the email content
    }
}
