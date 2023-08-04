<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class paymentmaster extends Model
{   use HasFactory;
    protected $table = 'payment_master';
    protected $fillable = ['Payment_Date', 'User_ID', 'employee_id', 'Company_Code', 'Comments', 'created_at', 'updated_at', 'ExtraDsscription'];

    
    public function employee(){
        return $this->belongsTo('App\Models\Employee','employee_id');
    }


    public function company(){
        return $this->belongsTo('App\Models\Company','Company_Code');
    }

    public function details(){
        return $this->hasOne('App\Models\paymentdetails','payment_master_id');
    }
}
