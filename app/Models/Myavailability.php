<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Myavailability extends Model
{
    use HasFactory,LogsActivity;
    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->setDescriptionForEvent(fn (string $eventName) => "Avaiability has been {$eventName}")
            ->useLogName(auth()->user()->company_roles->first()->company_code)
        ->logAll()
        ->logExcept(['updated_at','created_at','company_code'])
        ->logOnlyDirty();
        // Chain fluent methods for configuration options
    }

    public function employee()
    {
        return $this->belongsTo('App\Models\Employee', 'employee_id');
    }
    
}
