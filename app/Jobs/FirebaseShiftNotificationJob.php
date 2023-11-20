<?php

namespace App\Jobs;

use App\Models\TimeKeeper;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FirebaseShiftNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $timekeeper_id, $firebase;
    public function __construct($firebase, $timekeeper_id)
    {
        $this->firebase = $firebase;
        $this->timekeeper_id = $timekeeper_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $timekeeper = TimeKeeper::find($this->timekeeper_id);
        $title = '';
        $msg = '';
        if ($timekeeper) {
            $ext = Carbon::parse($timekeeper->roaster_date)->format('d-m-Y').'('.Carbon::parse($timekeeper->shift_start)->format('H:i').'-'.Carbon::parse($timekeeper->shift_end)->format('H:i').')'.' @'.$timekeeper->project->pName;
            if ($timekeeper->shift_end <= Carbon::now()->subMinutes(15) && $timekeeper->sing_in != null && $timekeeper->sing_out == null) {
                $title= 'Sign out alert :';
                $msg = "Please don't forgot to clock off for ".$ext;
            } elseif ($timekeeper->shift_end <= Carbon::now() && $timekeeper->sing_in != null && $timekeeper->sing_out == null) {
                $title= 'Sign out alert :';
                $msg = "Please don't forgot to clock off for ".$ext;
            } elseif ($timekeeper->shift_start <= Carbon::now()->subMinutes(10) && $timekeeper->sing_in == null) {
                $title= 'Sign in Alert :';
                $msg = 'Are you forget to sign in? please sign now. '.$ext;
            } elseif ($timekeeper->shift_start <= Carbon::now()->addMinutes(15) && $timekeeper->sing_in == null) {
                $title= 'Sign in Alert :';
                $msg = "Please don't forgot to sign in ".$ext;
            } elseif ($timekeeper->shift_start <= Carbon::now()->addHour() && $timekeeper->sing_in == null) {
                $title= 'Sign in Alert :';
                $msg = "Please don't forgot to sign in ".$ext;
            }
            push_notify($title, $msg,$timekeeper->employee->employee_role, $this->firebase,'user-dashboard');
            Log::alert($msg);
        } else {
            Log::alert('Notificatin not sent!');
        }
    }
}
