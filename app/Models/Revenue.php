<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Facades\Auth;

class Revenue extends Model
{
    use HasFactory,LogsActivity;
    protected $guarded = [];
    
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->setDescriptionForEvent(fn (string $eventName) => "Revenue has been {$eventName}")
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
}
