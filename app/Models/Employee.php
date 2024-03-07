<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Employee extends Model
{
  use HasFactory, LogsActivity;
  protected $guarded = [];

  /**
   * Get the user's image.
   *
   * @return \Illuminate\Database\Eloquent\Casts\Attribute
   */
  protected function image(): Attribute
  {
    return Attribute::make(
      get: fn ($value) => $value ? asset($value) : "",
    );
  }
  /**
   * Interact with the user's fullname.
   *
   * @return  \Illuminate\Database\Eloquent\Casts\Attribute
   */
  protected function fullname(): Attribute
  {
    return Attribute::make(
      get: fn ($value, $attributes) => ucwords($attributes['fname'] . ' ' . $attributes['mname'] . ' ' . $attributes['lname']),
    );
  }

  public function getActivitylogOptions(): LogOptions
  {
    return LogOptions::defaults()
      ->setDescriptionForEvent(fn (string $eventName) => "Employee has been {$eventName}")
      ->useLogName(auth()->user()->company_roles->first()->company_code)
      ->logAll()
      ->logExcept(['updated_at', 'created_at', 'id', 'company', 'user_id', 'userID'])
      ->logOnlyDirty();
    // Chain fluent methods for configuration options
  }

  //notification passing role
  public function employee_role()
  {
    return $this->belongsTo('App\Models\UserRole', 'userID', 'user_id')
      ->where('role', 3);
  }
  public function shiftDetails()
  {
    return $this->hasMany(TimeKeeper::class, 'employee_id');
  }
  public function admin()
  {
    return $this->belongsTo('App\Models\User', 'user_id', 'id');
  }

  public function user()
  {
    return $this->belongsTo('App\Models\User', 'userID', 'id');
  }

  public function compliances()
  {
    return $this->hasMany('App\Models\UserCompliance', 'user_id', 'userID');
  }

  public function firebase()
  {
    return $this->hasMany('App\Models\FirebaseToken', 'user_id', 'userID');
  }
}
