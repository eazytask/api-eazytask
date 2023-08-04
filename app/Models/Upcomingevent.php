<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Upcomingevent extends Model
{
    use HasFactory,LogsActivity;
    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->setDescriptionForEvent(fn (string $eventName) => "Upcoming event has been {$eventName}")
            ->useLogName(auth()->user()->company_roles->first()->company_code)
        ->logAll()
        ->logExcept(['updated_at','created_at','company_code'])
        ->logOnlyDirty();
        // Chain fluent methods for configuration options
    }

    public function client()
    {
        return $this->belongsTo('App\Models\Client', 'client_name');
    }

    public function project()
    {
        return $this->belongsTo('App\Models\Project', 'project_name');
    }
    public function job_type()
    {
        return $this->belongsTo('App\Models\JobType', 'job_type_name');
    }
    public function employee()
    {
        return $this->belongsTo('App\Models\Employee', 'employee_id');
    }
    public function event_requests()
    {
        return $this->hasMany('App\Models\Eventrequest', 'event_id');
    }
    public function already_applied()
    {
        return $this->hasMany('App\Models\Eventrequest', 'event_id')->where('employee_id',Auth::user()->employee->id);
    }
}
