<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\admin\TimekeeperResource;
use App\Http\Resources\admin\ViewScheduleResource;
use App\Models\Project;
use App\Models\TimeKeeper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ViewScheduleController extends Controller
{
    public function index(Request $request)
    {
        $fromDate = $request->start_date ? $request->start_date : Carbon::now()->startOfMonth();
        $toDate = $request->end_date ? $request->end_date : Carbon::now()->endOfMonth();

        $schedule = $request->roaster_type;
        $employee_id = $request->employee_id;
        $project_id = $request->project_id;

        $filter_roaster_type = $schedule ? ['roaster_type', $schedule] : ['employee_id', '>', 0];
        $filter_employee = $employee_id ? ['employee_id', $employee_id] : ['employee_id', '>', 0];
        $filter_project = $project_id ? ['project_id', $project_id] : ['employee_id', '>', 0];

        $employees = DB::table('time_keepers')
            ->select(DB::raw(
                'e.id as employee_id,
                e.image,
                e.fname,
                e.mname,
                e.lname,
                sum(time_keepers.duration) as total_hours,
                sum(time_keepers.amount) as total_amount ,
                count(time_keepers.id) as record'

            ))
            ->leftJoin('employees as e', 'e.id', 'time_keepers.employee_id')
            ->where([
                ['e.company', Auth::user()->company_roles->first()->company->id],
                ['e.role', 3]
            ])
            ->groupBy("e.id")
            ->orderBy('e.fname', 'asc')
            ->whereBetween('roaster_date', [Carbon::parse($fromDate), Carbon::parse($toDate)])
            ->where([
                ['company_code', Auth::user()->company_roles->first()->company->id],
                $filter_roaster_type,
                $filter_employee,
                $filter_project,
            ])
            ->where(function ($q) {
                avoid_rejected_key($q);
            })
            ->get();

        return send_response(true, '', ViewScheduleResource::collection($employees));
    }

    public function get_rosters(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'employee_id' => 'required',
            ]);
            if ($validator->fails())
                return send_response(false, 'validation error!', $validator->errors(), 400);


            $fromDate = $request->start_date ? $request->start_date : Carbon::now()->startOfMonth();
            $toDate = $request->end_date ? $request->end_date : Carbon::now()->startOfMonth();

            $schedule = $request->roaster_type;
            $project_id = $request->project_id;

            $filter_roaster_type = $schedule ? ['roaster_type', $schedule] : ['employee_id', '>', 0];
            $filter_project = $project_id ? ['project_id', $project_id] : ['employee_id', '>', 0];

            $rosters = TimeKeeper::where([
                ['employee_id', $request->employee_id],
                ['company_code', Auth::user()->company_roles->first()->company->id],
                $filter_roaster_type,
                $filter_project
            ])
                ->orderBy('roaster_date', 'asc')
                ->orderBy('shift_start', 'asc')
                ->whereBetween('roaster_date', [Carbon::parse($fromDate), Carbon::parse($toDate)])
                ->where(function ($q) {
                    avoid_rejected_key($q);
                })
                ->get();
            return send_response(true, '', TimekeeperResource::collection($rosters));
        } catch (\Throwable $e) {
            return send_response(false, 'something went wrong!', 400);
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'timekeeper_id' => 'required',
            'app_start' => 'required',
            'app_end' => 'required',
            'app_duration' => 'required',
            'app_rate' => 'required',
            'app_amount' => 'required',
            'is_approved' => 'required',
        ]);

        if ($validator->fails())
            return send_response(false, 'validation error!', $validator->errors(), 400);

        try {
            $timekeeper = TimeKeeper::find($request->timekeeper_id);
        

            if (isset($timekeeper)) {
                if ($timekeeper->shift_end <= Carbon::now() && $timekeeper->is_approved == 0) {
                    $shift_start = Carbon::parse($timekeeper->roaster_date . $request->app_start);
                    $shift_end = Carbon::parse($shift_start)->addMinute($request->app_duration * 60);

                    $timekeeper->Approved_start_datetime = $shift_start;
                    $timekeeper->Approved_end_datetime = $shift_end;
                    $timekeeper->app_duration = $request->app_duration;
                    $timekeeper->app_rate = $request->app_rate;
                    $timekeeper->app_amount = $request->app_amount;
                    if(isset($request->sing_in)){
                        $timekeeper->sing_in = Carbon::parse($request->sing_in);
                    }
                    if(isset($request->sing_out)){
                        $timekeeper->sing_out = Carbon::parse($request->sing_out);
                    }
                    if($request->is_approved == 1){
                        $timekeeper->is_approved = 1;
                    }
                    $timekeeper->save();
                    return send_response(true, 'roster updated successfully', new TimekeeperResource($timekeeper));
                }
            }
            return send_response(false, 'roster updated successfully', new TimekeeperResource($timekeeper));
        } catch (\Throwable $e) {
            return send_response(false, $e->getMessage(), [], 400);
        }
    }

    public function approve($ids)
    {
        $ids = explode(',', $ids);
        foreach ($ids as $id) {
            $timekeeper = TimeKeeper::find($id);
            if ($timekeeper) {
                $timekeeper->is_approved = 1;
                $timekeeper->save();
            }
        }
        return send_response(true, 'rosters approved successfully');
    }
}
