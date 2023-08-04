<?php

namespace App\Observers;

use App\Mail\NotifyNewEvent;
use App\Models\Employee;
use App\Models\UpcomingEvent;
use App\Notifications\NewEventNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class UpcomingEventObserver
{
    /**
     * Handle the UpcomingEvent "created" event.
     *
     * @param  \App\Models\UpcomingEvent  $upcomingEvent
     * @return void
     */
    public function created(UpcomingEvent $upcomingEvent)
    {
        $this->sent_notification($upcomingEvent);
    }

    /**
     * Handle the UpcomingEvent "updated" event.
     *
     * @param  \App\Models\UpcomingEvent  $upcomingEvent
     * @return void
     */
    public function updated(UpcomingEvent $upcomingEvent)
    {
        $this->sent_notification($upcomingEvent,true);
    }

    /**
     * Handle the UpcomingEvent "deleted" event.
     *
     * @param  \App\Models\UpcomingEvent  $upcomingEvent
     * @return void
     */
    public function deleted(UpcomingEvent $upcomingEvent)
    {
        //
    }

    /**
     * Handle the UpcomingEvent "restored" event.
     *
     * @param  \App\Models\UpcomingEvent  $upcomingEvent
     * @return void
     */
    public function restored(UpcomingEvent $upcomingEvent)
    {
        //
    }

    /**
     * Handle the UpcomingEvent "force deleted" event.
     *
     * @param  \App\Models\UpcomingEvent  $upcomingEvent
     * @return void
     */
    public function forceDeleted(UpcomingEvent $upcomingEvent)
    {
        //
    }

    protected function sent_notification($event,$ext=false){
        if(Carbon::parse($event->event_date)->toDateString() != Carbon::now()->toDateString()){
            $pro = $event->project;
            $ext = $ext?' updated':'';
            $msg = 'There is an event'.$ext.' "'.$pro->pName.'" on ' . Carbon::parse($event->event_date)->format('d-m-Y') . ' (' . Carbon::parse($event->shift_start)->format('H:i') . '-' . Carbon::parse($event->shift_end)->format('H:i') . ') near "'.$pro->project_address.' '.$pro->suburb.' '.$pro->project_state.'"';
    
            $employees = Employee::where([
                ['company', Auth::user()->company_roles->first()->company->id],
                ['role', 3],
                ['status', 1]
            ])
                ->where(function ($q) {
                    avoid_expired_license($q);
                })
                ->get();
    
            foreach ($employees as $emp) {
                $emp->user->notify(new NewEventNotification($msg));
                push_notify('Event Alert :', $msg.'. If you interested please open app and send event request',$emp->employee_role, $emp->firebase,'user-event',$event->id);

                try {
                    Mail::to($emp->user->email)->send(new NotifyNewEvent($emp, $msg));
                } catch (\Exception $e) {
                }
            }
        }
    }
}
