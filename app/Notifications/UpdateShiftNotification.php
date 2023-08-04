<?php

namespace App\Notifications;

use App\Mail\NotifyUpdateShift;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Mail;

class UpdateShiftNotification extends Notification
{
    use Queueable;
    protected $msg,$shift,$ext;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($msg,$shift,$ext='updated')
    {
        $this->msg = $msg;
        $this->shift = $shift;
        $this->ext = $ext;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        try {
            Mail::to($this->shift->employee->user->email)->send(new NotifyUpdateShift($this->shift->employee, $this->shift,$this->ext));
        } catch (\Exception $e) {
        }

        return [
            'type' => 'Shift Update :',
            'status' => '',
            'msg' => $this->msg,
        ];
    }
}
