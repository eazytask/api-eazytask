<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Notifications\LicenseExpiredNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckEmployeeLicense extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:license';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'check all employee license';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $employees = Employee::all();
        foreach($employees as $employee){
            $name = "$employee->fname $employee->mname $employee->lname";
            $name = ucwords($name);
            
            #checking security license
            $license = $employee->license_expire_date;
            if($license && $license < Carbon::now()->toDateString() ){
                $employee->user->notify(new LicenseExpiredNotification('Please renew your security license'));
                $employee->admin->notify(new LicenseExpiredNotification("Please renew $name's security license"));
            }elseif($license && $license <= Carbon::now()->addDays(7)->toDateString() ){
                $employee->user->notify(new LicenseExpiredNotification('Your security license is going to expired'));
                $employee->admin->notify(new LicenseExpiredNotification("$name's security license is going to expired"));
            }

            #check first aid
            $first_aid = $employee->first_aid_expire_date;
            if($first_aid && $first_aid < Carbon::now()->toDateString() ){
                $employee->user->notify(new LicenseExpiredNotification('Please renew your first-aid license'));
                $employee->admin->notify(new LicenseExpiredNotification("Please renew $name's first-aid license"));
            }elseif($first_aid && $first_aid <= Carbon::now()->addDays(7)->toDateString() ){
                $employee->user->notify(new LicenseExpiredNotification('Your first-aid license is going to expired'));
                $employee->admin->notify(new LicenseExpiredNotification("$name's first-aid license is going to expired"));
            }
        }

        $this->info('successfully checked all employees License & First-Aid');
    }
}
