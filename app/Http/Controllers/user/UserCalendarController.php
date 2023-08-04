<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use App\Http\Resources\user\UserCalendarEventResource;
use App\Http\Resources\user\UserCalendarResource;
use App\Http\Resources\user\UserTimekeeperResource;
use App\Models\TimeKeeper;
use App\Models\Upcomingevent;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserCalendarController extends Controller
{
    public function index(Request $request){
        $filter_project = $request->project_id ? ['project_id', $request->project_id] : ['id', '>', 0];
        $month = Carbon::parse($request->month);
        $timekeepers = TimeKeeper::where([
            $filter_project,
            ['employee_id', Auth::user()->employee->id],
            ['company_code', Auth::user()->employee->company],
        ])
        ->where(function ($q) {
            $q->where('roaster_type', 'Unschedueled');
            $q->orWhere(function ($q) {
                $q->where('roaster_status_id', '!=', roaster_status('Not published'));
            });
        })
        ->where(function ($q) {
            avoid_rejected_key($q);
        })
        ->whereBetween('roaster_date', [$month->startOfMonth()->toDateString(), $month->endOfMonth()->toDateString()])
        ->get();

        $e_filter_project = $request->project_id ? ['project_name', $request->project_id] : ['id', '>', 0];
        $events = Upcomingevent::where([
            $e_filter_project,
            ['company_code', Auth::user()->company_roles->first()->company->id]
        ])
        ->whereBetween('event_date', [$month->startOfMonth()->toDateString(), $month->endOfMonth()->toDateString()])
        ->get();

        $event= UserCalendarEventResource::collection($events);

        return send_response(true, '', UserCalendarResource::collection($timekeepers)->merge($event));
    }


    // public function show($roster_id){
    //     try {
    //         $timekeeper = TimeKeeper::where([
    //             ['employee_id', Auth::user()->employee->id],
    //             ['company_code', Auth::user()->employee->company],
    //             ['id',$roster_id]
    //         ])->first();
    //         return send_response(true, '', new UserTimekeeperResource($timekeeper));
    //     } catch (\Throwable $e) {
    //         return send_response(false,$e->getMessage(),'',422);
    //     }
    // }
}
