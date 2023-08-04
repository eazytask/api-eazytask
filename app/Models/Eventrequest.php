<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Eventrequest extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function upcomingevent()
    {
        return $this->belongsTo('App\Models\Upcomingevent',  'event_id', 'id');
    }
    public function employee()
    {
        return $this->belongsTo('App\Models\Employee',  'employee_id', 'userID');
    }
}
