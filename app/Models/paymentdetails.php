<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class paymentdetails extends Model
{
    use HasFactory;
    protected $table = 'payment_details';
    protected $fillable = [ 'payment_master_id',
    'Timekeeper_ID',
    'Client_ID',
    'Project_ID',
    'Roaster_Date',
    'Approved_start_datetime',
    'Approved_end_datetime',
    'App_Duration',
    'App_Rate',
    'Other_Pay',
    'Approved_Amount',
    'Total_Pay',
    'Remarks',
    'PaymentMethod',
    'created_at','updated_at'];
}
