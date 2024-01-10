<?php

namespace App\Jobs;

use App\Http\Controllers\user\SignInController;
use App\Models\TimeKeeper;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AutoSignOutJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $timekeeper_id;
    
    public function __construct($timekeeper_id)
    {
        $this->timekeeper_id = $timekeeper_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $signIn = new SignInController;
        $signIn->addSignOut($this->timekeeper_id, true);
    }
}
