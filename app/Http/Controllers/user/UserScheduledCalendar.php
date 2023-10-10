<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use App\Http\Resources\admin\ScheduledCalendarResource;
use App\Http\Resources\user\UserProjectResource;
use App\Http\Resources\user\UserScheduledCalendarResource;
use App\Models\Project;
use App\Models\TimeKeeper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\user\UserTimekeeperResource;


class UserScheduledCalendar extends Controller
{
    public function get_projects($week)
    {
        $shift = DB::table('time_keepers')
            ->select(DB::raw(
                'p.*'
            ))
            ->leftJoin('projects as p', 'p.id', 'time_keepers.project_id')
            // ->groupBy("p.pName")
            ->whereBetween('roaster_date', [$week->startOfWeek()->toDateString(), $week->endOfWeek()->toDateString()])
            ->where([
                ['employee_id', Auth::user()->employee->id],
                // ['roaster_status_id', roaster_status('Accepted')],
                // ['roaster_type', 'Schedueled']
            ])
            ->where(function ($q) {
                avoid_rejected_key($q);
            })
            ->get();

        $inducted = DB::table('inductedsites')
            ->select(DB::raw(
                'p.*'
            ))
            ->leftJoin('projects as p', 'p.id', 'inductedsites.project_id')
            ->where([
                ['employee_id', Auth::user()->employee->id],
            ])
            ->get();

        $projects = $shift->merge($inducted);
        return $projects->unique('id')->values();
    }
    
    public function job_type(){
        return $this->belongsTo('App\Models\JobType','job_type_id');
    }

    public function index(Request $request)
    {
        $week = Carbon::parse($request->week);

        if (Auth::user()->company_roles->sortByDesc('last_login')->first()->role == 2) {

            $projects = Project::where([
                ['company_code', auth()->user()->company_roles->first()->company->id],
                ['status', 1]
            ])->get();
            // $filter_project = $request->project ? ['project_id', $request->project] : ['project_id', '>', 0];
            $filter_project = ['project_id', $request->project];
        } else {
            $projects = $this->get_projects($week);
            if (!$request->project) {
                $request->project = $projects->first() ? $projects->first()->id : '';
            }
            $filter_project = ['project_id', $request->project];
            // $filter_project = $request->project ? ['project_id', $request->project] : ['project_id', 0];
        }

        $start_date = Carbon::parse($week)->startOfWeek();
        $end_date = Carbon::parse($week)->endOfWeek();

        if (empty($request->project)) {
            return send_response(true, '', [
                'week' => $start_date->format('d M, Y') . ' -  ' . $end_date->format('d M, Y'),
                "current_project" => $request->project?(int)$request->project:null,
                "projects" => UserProjectResource::collection($projects),
                "job_type" => [],
                'data' => [],
            ]);
        }

        $mon_ = [];
        $tue_ = [];
        $wed_ = [];
        $thu_ = [];
        $fri_ = [];
        $sat_ = [];
        $sun_ = [];
        
        if (Auth::user()->company_roles->sortByDesc('last_login')->first()->role == 2){
            $timekeepers = TimeKeeper::where([
            // ['employee_id', $employee->id],
            ['time_keepers.company_code', Auth::user()->company_roles->first()->company->id],
            // ['time_keepers.roaster_type', 'Schedueled'],
            // $filter_roster_type,
            $filter_project
        ])->whereBetween('time_keepers.roaster_date', [$start_date, $end_date])
         ->with([
            'job_type' => function($q) {
                $q->select('id', 'name');
            }])
            ->orderBy('time_keepers.shift_start','ASC')
            ->join('roaster_statuses', 'time_keepers.roaster_status_id', '=', 'roaster_statuses.id')
            ->leftJoin('employees as e', 'e.id', 'time_keepers.employee_id')
                ->get(['roaster_statuses.color', 'time_keepers.*', DB::raw('CONCAT_WS(" ", e.fname, e.mname, e.lname) AS employee_name')]);
        }else{
            $timekeepers = TimeKeeper::where([
                // ['employee_id', $employee->id],
                ['time_keepers.company_code', Auth::user()->company_roles->first()->company->id],
                // ['time_keepers.roaster_type', 'Schedueled'],
                // $filter_roster_type,
                $filter_project
            ])->whereBetween('time_keepers.roaster_date', [$start_date, $end_date])
            ->with([
            'job_type' => function($q) {
                $q->select('id', 'name');
            }])
            ->orderBy('time_keepers.shift_start','ASC')
            ->join('roaster_statuses', 'time_keepers.roaster_status_id', '=', 'roaster_statuses.id')
            ->leftJoin('employees as e', 'e.id', 'time_keepers.employee_id')
                ->get(['roaster_statuses.color', 'time_keepers.*', DB::raw('CONCAT_WS(" ", e.fname, e.mname, e.lname) AS employee_name')]);
        }
 

        // $timekeepers = TimeKeeper::where([
        //     // ['employee_id', $employee->id],
        //     ['time_keepers.company_code', Auth::user()->company_roles->first()->company->id],
        //     ['time_keepers.roaster_type', 'Schedueled'],
        //     ['time_keepers.roaster_status_id', roaster_status('Accepted')],
        //     // $filter_roster_type,
        //     $filter_project
        // ])->whereBetween('time_keepers.roaster_date', [$start_date, $end_date])
        // ->join('roaster_statuses', 'time_keepers.roaster_status_id', '=', 'roaster_statuses.id')
        //     ->get(['roaster_statuses.color', 'time_keepers.*']);
        
        $job_type = array();
      

        foreach (ScheduledCalendarResource::collection($timekeepers) as $timekeeper) {
            $roaster_day = Carbon::parse($timekeeper->roaster_date)->format('D');

            if ($roaster_day == 'Mon') {
                array_push($mon_, $timekeeper);
            } elseif ($roaster_day == 'Tue') {
                array_push($tue_, $timekeeper);
            } elseif ($roaster_day == 'Wed') {
                array_push($wed_, $timekeeper);
            } elseif ($roaster_day == 'Thu') {
                array_push($thu_, $timekeeper);
            } elseif ($roaster_day == 'Fri') {
                array_push($fri_, $timekeeper);
            } elseif ($roaster_day == 'Sat') {
                array_push($sat_, $timekeeper);
            } elseif ($roaster_day == 'Sun') {
                array_push($sun_, $timekeeper);
            }
            
            if(!in_array($timekeeper->job_type, $job_type)) {
                array_push($job_type, $timekeeper->job_type);
            }
            
        }

        return send_response(true, '', [
            'week' => $start_date->format('d M, Y') . ' -  ' . $end_date->format('d M, Y'),
            "current_project" => $request->project?(int)$request->project:null,
            "projects" => UserProjectResource::collection($projects),
            "job_type" => $job_type,
            'data' => [
                [
                    "day" => "Monday",
                    "shifts" => UserTimekeeperResource::collection($mon_)
                ],
                [
                    "day" => "Tuesday",
                    "shifts" => UserTimekeeperResource::collection($tue_)
                ],
                [
                    "day" => "Wednesday",
                    "shifts" => UserTimekeeperResource::collection($wed_)
                ],
                [
                    "day" => "Thursday",
                    "shifts" => UserTimekeeperResource::collection($thu_)
                ],
                [
                    "day" => "Friday",
                    "shifts" => UserTimekeeperResource::collection($fri_)
                ],
                [
                    "day" => "Saturday",
                    "shifts" => UserTimekeeperResource::collection($sat_)
                ],
                [
                    "day" => "Sunday ",
                    "shifts" => UserTimekeeperResource::collection($sun_)
                ],
            ]
        ]);
    }
}
