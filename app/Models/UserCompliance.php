<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class UserCompliance extends Model
{
    use HasFactory,LogsActivity;
    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->setDescriptionForEvent(fn (string $eventName) => "User compliance has been {$eventName}")
            ->useLogName(auth()->user()->company_roles->first()->company_code)
        ->logAll()
        ->logExcept(['updated_at','created_at','company_code'])
        ->dontSubmitEmptyLogs()
        ->logOnlyDirty();
        // Chain fluent methods for configuration options
    }

    public function compliance()
    {
        return $this->belongsTo('App\Models\Compliance');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function employees_of_user()
    {
        return $this->hasMany('App\Models\Employee','userID','user_id');
    }
}
