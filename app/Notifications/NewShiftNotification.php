<?php

namespace App\Notifications;

use App\Mail\NotifyUser;
use App\Models\TimeKeeper;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class NewShiftNotification extends Notification
{
    use Queueable;
    protected $msg,$shift;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($msg,$shift)
    {
        $this->msg = $msg;
        $this->shift = $shift;
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
        $timekeepers = TimeKeeper::where([
            ['company_code', Auth::user()->company_roles->first()->company->id],
            ['employee_id', $this->shift->employee_id],
            ['roaster_type', 'Schedueled'],
            ['shift_end','>',Carbon::now()]
        ])
        ->where(function ($q){
            $q->where('roaster_status_id', roaster_status('Accepted'));
            $q->orWhere(function ($q) {
                $q->where('roaster_status_id', roaster_status('Published'));
            });
        })
            ->whereBetween('roaster_date', [Carbon::parse($this->shift->roaster_date)->startOfWeek(), Carbon::parse($this->shift->roaster_date)->endOfWeek()])
            ->get();
            try {
                Mail::to($this->shift->employee->user->email)->send(new NotifyUser($this->shift->employee, $timekeepers));
            } catch (\Exception $e) {
            }

        return [
            'type' => 'Shift Alert :',
            'status' => '',
            'msg' => $this->msg,
        ];
    }
}
