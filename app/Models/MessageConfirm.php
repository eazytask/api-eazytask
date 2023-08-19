<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageConfirm extends Model
{
    use HasFactory;
    protected $table = "message_confirms";
    protected $fillable = ['user_id', 'message_id'];

    protected $appends = ['fullname'];

    public function getFullnameAttribute()
    {
        return $this->user->name.' '.$this->user->mname.' '.$this->user->lname;
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
