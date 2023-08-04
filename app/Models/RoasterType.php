<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoasterType extends Model
{
    use HasFactory;
    // protected $table ="roaster_types";
    protected $guarded =[];
    public function roster(){
        return $this->belongsTo('App\Models\TimeKeeper','roaster_id');
    }
}
