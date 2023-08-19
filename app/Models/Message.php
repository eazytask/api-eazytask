<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;
    protected $table = "messages";
    protected $fillable = ['user_id', 'heading', 'text', 'published', 'need_confirm', 'text', 'publish_date', 'list_venue'];

    protected $casts = [
        'list_venue' => 'json',
    ];

    protected $appends = ['fullname'];

    public function getFullnameAttribute()
    {
        return $this->user->name.' '.$this->user->mname.' '.$this->user->lname;
    }

    public function getListVenue()
    {
        return \App\Models\Project::whereIn('id', $this->list_venue ?? [])->get()->pluck('pName');
    }

    public function containVenue($venueId)
    {
        return $this->whereJsonContains('list_venue', $venueId)->get();
    }

    public function replies()
    {
        return $this->hasMany('App\Models\MessageReply')->orderBy('created_at', 'DESC');
    }

    public function confirms()
    {
        return $this->hasMany('App\Models\MessageConfirm');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
