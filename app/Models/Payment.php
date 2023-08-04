<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    protected $table = "payments";

    public function roster()
    {
        return $this->belongsTo('App\Models\TimeKeeper', 'roaster_id');
    }
}
