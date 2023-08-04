<?php

namespace App\Providers;

use App\Models\TimeKeeper;
use App\Models\Upcomingevent;
use App\Observers\TimeKeeperObserver;
use App\Observers\UpcomingEventObserver;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Console\ClientCommand;
use Laravel\Passport\Console\InstallCommand;
use Laravel\Passport\Console\KeysCommand;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        TimeKeeper::observe(TimeKeeperObserver::class);
        Upcomingevent::observe(UpcomingEventObserver::class);
        $this->commands([
            InstallCommand::class,
            ClientCommand::class,
            KeysCommand::class,
        ]);
        
        JsonResource::withoutWrapping();
    }
}
