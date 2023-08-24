<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Project extends Model
{
    use HasFactory, LogsActivity;
    protected $table = "projects";

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'user_id' => 'string',
        'clientName' => 'string',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->setDescriptionForEvent(fn (string $eventName) => "Project has been {$eventName}")
            ->useLogName(auth()->user()->company_roles->first()->company_code)
        ->logAll()
        ->logExcept(['updated_at','created_at','company_code'])
        ->logOnlyDirty();
        // Chain fluent methods for configuration options
    }

    public function client()
    {
        return $this->belongsTo('App\Models\Client', 'clientName');
    }
}
