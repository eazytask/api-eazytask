<?php

namespace App\Console\Commands;

use App\Models\Compliance;
use App\Models\UserCompliance;
use App\Notifications\LicenseExpiredNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckCompliance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:compliance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'check all compliance';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $compliances = UserCompliance::all();
        foreach ($compliances as $com) {
            $name = $com->user->name . ' ' . $com->user->mname . ' ' . $com->user->lname;
            $name = ucwords($name);

            #checking security license
            $license = $com->expire_date;
            if ($license && $license < Carbon::now()->toDateString()) {
                $com->user->notify(new LicenseExpiredNotification('Please renew your compliance ' . $com->compliance->name));
                foreach ($com->employees_of_user as $emp) {
                    $emp->admin->notify(new LicenseExpiredNotification("Please renew $name's compliance " . $com->compliance->name));
                }
            } elseif ($license && $license <= Carbon::now()->addDays(7)->toDateString()) {
                $com->user->notify(new LicenseExpiredNotification('Your compliance ' . $com->compliance->name . ' is going to expired!'));
                foreach ($com->employees_of_user as $emp) {
                    $emp->admin->notify(new LicenseExpiredNotification("$name's compliance " . $com->compliance->name . " is going to expired!"));
                }
            }
        }

        $this->info('successfully checked all compliances');
    }
}
