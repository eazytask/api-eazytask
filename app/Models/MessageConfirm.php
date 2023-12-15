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
        // Create an array with all name parts
        $nameParts = [
            $this->user->name,
            $this->user->mname,
            $this->user->lname,
        ];

        // Use array_filter to remove null values
        $filteredNameParts = array_filter($nameParts, function ($part) {
            return $part !== null;
        });

        // Concatenate the filtered name parts with a space
        $fullName = implode(' ', $filteredNameParts);

        return $fullName;
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
