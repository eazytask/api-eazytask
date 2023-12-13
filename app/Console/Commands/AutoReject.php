<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Session;
use App\Models\TimeKeeper;
use App\Models\RoasterStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Notifications\ConfirmShiftNotification;
use App\Models\User;

class AutoReject extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reject:auto';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reject timekeeper that not accepted';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $action = 'reject';
        // $status = Session::get('roaster_status')['Rejected'];

        $reject_status_id = RoasterStatus::where('name', 'Published')->get()->pluck('id');
        
        $timekeepers = TimeKeeper::where('shift_end', '<', Carbon::now())->whereIn('roaster_status_id', $reject_status_id)->get();

        foreach($timekeepers as $timekeeper){
            $roster = TimeKeeper::find($timekeeper->id);
            // $roster->roaster_status_id = $status;
            $roster->delete();

            // $admin = User::find($roster->user_id);
            // $admin->notify(new ConfirmShiftNotification($roster));

            $confirm = 'Rejected';
            $status = 'danger';
            
            $msg = 'rejected a shift of week ending '. Carbon::parse($timekeeper->roaster_date)->endOfWeek()->format('d-m-Y');
            
            $admin = User::find($timekeeper->user_id);
            // $admin->notify(new ConfirmShiftNotification($msg,$action,'reject'));
            // push_notify($action.' Shift :',$msg. ' Please check eazytask for changes',$admin->admin_role,$admin->firebase,'admin-scheduele-entry');
        }
        
        $this->info('successfully checked auto reject timekeeper');
    }
}
