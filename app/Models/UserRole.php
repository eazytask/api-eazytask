<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    use HasFactory;
    protected $fillable = ['role','company_code','user_id','status'];

    public function company()
    {
        return $this->hasOne('App\Models\Company', 'id', 'company_code');
    }
}
