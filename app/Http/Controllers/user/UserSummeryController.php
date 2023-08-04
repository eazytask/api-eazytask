<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use App\Models\TimeKeeper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserSummeryController extends Controller
{
    public function index(){
        $notification = Auth::user()->unreadNotifications->count();
        $unconfirmed = TimeKeeper::where([
            ['employee_id', Auth::user()->employee->id],
            ['company_code', Auth::user()->employee->company],
            ['roaster_status_id', roaster_status('Published')],
            ['shift_start', '>=', Carbon::now()],
        ])
        ->count();
        $upcoming = TimeKeeper::where([
            ['employee_id', Auth::user()->employee->id],
            ['company_code', Auth::user()->employee->company],
            ['roaster_status_id', roaster_status('Accepted')],
            ['shift_end', '>=', Carbon::now()],
            ['sing_in', null]
        ])
        ->whereBetween('roaster_date', [Carbon::now()->startOfWeek()->toDateString(), Carbon::now()->addMonths(2)->endOfMonth()->endOfWeek()->toDateString()])
        ->orderBy('roaster_date', 'asc')
        ->count();

        $data=[
            "notification"=>$notification,
            "unconfirmed"=>$unconfirmed,
            "upcoming"=>$upcoming
        ];
        return send_response(true, '',$data);
    }
}
