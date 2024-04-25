<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class TimeKeeper extends Model
{
    use HasFactory, LogsActivity;
    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    { 
        return LogOptions::defaults()
        ->setDescriptionForEvent(fn(string $eventName) => "Roster has been {$eventName}")
        ->useLogName($this->company_id)
        ->logAll()
        ->logExcept(['updated_at','created_at','company_code','Approved_start_datetime','Approved_end_datetime'])
        ->logOnlyDirty();
        // Chain fluent methods for configuration options
    }


    public function employee(){
        return $this->belongsTo('App\Models\Employee','employee_id');
    }
    public function shiftDetails(){
        return $this->belongsTo('App\Models\Employee','employee_id')->with('shiftDetails');
    }
    public function client(){
        return $this->belongsTo('App\Models\Client','client_id');
    }
    public function project(){
        return $this->belongsTo('App\Models\Project','project_id');
    }
    public function roaster_status(){
        return $this->belongsTo('App\Models\RoasterStatus','roaster_status_id');
    }
    public function job_type(){
        return $this->belongsTo('App\Models\JobType','job_type_id');
    }
}
