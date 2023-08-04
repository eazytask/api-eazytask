<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Client extends Model
{
  use HasFactory, LogsActivity;
  protected $table = "clients";

  /**
   * Get the user's image.
   *
   * @return \Illuminate\Database\Eloquent\Casts\Attribute
   */
  protected function cimage(): Attribute
  {
    return Attribute::make(
      get: fn ($value) => $value ? asset($value) : '',
    );
  }

  public function getActivitylogOptions(): LogOptions
  {
    return LogOptions::defaults()
      ->setDescriptionForEvent(fn (string $eventName) => "Client has been {$eventName}")
      ->useLogName(auth()->user()->company_roles->first()->company_code)
      ->logAll()
      ->logExcept(['updated_at', 'created_at', 'company_code'])
      ->logOnlyDirty();
    // Chain fluent methods for configuration options
  }
}
