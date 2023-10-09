<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\admin\ScheduledCalendarResource;
use App\Http\Resources\admin\AdminRosterResource;
use App\Http\Resources\admin\EmployeeResource;
use App\Http\Resources\user\UserProjectResource;
use App\Mail\NotifyUser;
use App\Models\Employee;
use App\Models\Myavailability;
use App\Models\Project;
use App\Models\TimeKeeper;
use App\Models\User;
use App\Notifications\NewShiftNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ScheduledCalendarController extends Controller
{
    public function filter_emoployee(Request $request)
    {
        $employees = [];
        $start = $request->shift_start ? $request->shift_start : Carbon::now()->format('h:i');
        $end = $request->shift_end ? $request->shift_end : Carbon::now()->format('h:i');

        $shift_start = Carbon::parse($request->roster_date . $start);
        $shift_end = Carbon::parse($request->roster_date . $end);

        if ($request->filter == 'inducted') {
            if ($request->project) {
                $all_employees = DB::table('inductedsites')
                    ->select(DB::raw(
                        'e.* ,
                        e.fname as name'
                    ))
                    ->leftJoin('employees as e', 'e.id', 'inductedsites.employee_id')
                    ->where([
                        ['e.company', Auth::user()->company_roles->first()->company->id],
                        ['e.role', 3],
                        ['e.status', '1'],
                        ['project_id', $request->project]
                    ])
                    ->where(function ($q) {
                        e_avoid_expired_license($q);
                    })
                    ->orderBy("e.fname")
                    ->get();

                #check availability
                $avail_employees = [];
                foreach ($all_employees as $row) {
                    $availity = Myavailability::where([
                        ['company_code', Auth::user()->company_roles->first()->company->id],
                        ['employee_id', $row->id],
                    ])
                        ->where(function ($q) use ($shift_start, $shift_end) {
                            $q->where('start_date', '>=', $shift_start);
                            $q->where('start_date', '<=', $shift_end);
                            $q->orWhere(function ($q) use ($shift_end, $shift_start) {
                                $q->where('end_date', '>=', $shift_start);
                                $q->where('end_date', '<=', $shift_end);
                            });
                        })
                        ->first();

                    if (!$availity) {
                        array_push($avail_employees, $row);
                    }
                }

                #check has any roster
                $employees = [];
                foreach ($avail_employees as $row) {
                    $timekeeper = TimeKeeper::where([
                        ['company_code', Auth::user()->company_roles->first()->company->id],
                        ['employee_id', $row->id]
                    ])
                        ->where(function ($q) use ($shift_start, $shift_end) {
                            $q->where('shift_start', '>=', $shift_start);
                            $q->where('shift_start', '<=', $shift_end);
                            $q->orWhere(function ($q) use ($shift_end, $shift_start) {
                                $q->where('shift_end', '>=', $shift_start);
                                $q->where('shift_end', '<=', $shift_end);
                            });
                        })
                        ->first();

                    if (!$timekeeper) {
                        array_push($employees, $row);
                    }
                }
            }
        } elseif ($request->filter == 'available') {
            $all_employees = Employee::where([
                ['company', Auth::user()->company_roles->first()->company->id],
                ['role', 3],
                ['status', '1']
            ])
                ->where(function ($q) {
                    avoid_expired_license($q);
                })
                ->orderBy("fname")
                ->get();

            $avail_employees = [];
            foreach ($all_employees as $row) {
                $availity = Myavailability::where([
                    ['company_code', Auth::user()->company_roles->first()->company->id],
                    ['employee_id', $row->id],
                ])
                    ->where(function ($q) use ($shift_start, $shift_end) {
                        $q->where('start_date', '>=', $shift_start);
                        $q->where('start_date', '<=', $shift_end);
                        $q->orWhere(function ($q) use ($shift_end, $shift_start) {
                            $q->where('end_date', '>=', $shift_start);
                            $q->where('end_date', '<=', $shift_end);
                        });
                    })
                    ->first();

                if (!$availity) {
                    array_push($avail_employees, $row);
                }
            }

            $employees = [];
            foreach ($avail_employees as $row) {
                $timekeeper = TimeKeeper::where([
                    ['company_code', Auth::user()->company_roles->first()->company->id],
                    ['employee_id', $row->id]
                ])
                    ->where(function ($q) use ($shift_start, $shift_end) {
                        $q->where('shift_start', '>=', $shift_start);
                        $q->where('shift_start', '<=', $shift_end);
                        $q->orWhere(function ($q) use ($shift_end, $shift_start) {
                            $q->where('shift_end', '>=', $shift_start);
                            $q->where('shift_end', '<=', $shift_end);
                        });
                    })
                    ->first();

                if (!$timekeeper) {
                    array_push($employees, $row);
                }
            }
        } else {
            $employees = Employee::where([
                ['company', Auth::user()->company_roles->first()->company->id],
                ['status', '1'],
                ['role', 3]
            ])
                ->where(function ($q) {
                    avoid_expired_license($q);
                })
                ->orderBy('fname', 'asc')->get();
        }

        return send_response(true, '', EmployeeResource::collection($employees));
    }
    
    public function roaster_status(){
        return $this->belongsTo('App\Models\RoasterStatus','roaster_status_id');
    }

    public function get_roster_enrty(Request $request)
    {
        $week = Carbon::parse($request->week);
        $filter_project = $request->project ? ['project_id', $request->project] : ['employee_id', '>', 0];
        $filter_roster_status = $request->roster_status ? ['roaster_status_id', $request->roster_status] : ['employee_id', '>', 0];
        // $filter_roster_type = $request->roster_type? ['roaster_type', $request->roster_type] : ['employee_id', '>', 0];

        $start_date = Carbon::parse($week)->startOfWeek();
        $end_date = Carbon::parse($week)->endOfWeek();
        
        $projects = Project::whereHas('client', function ($query) {
            $query->where('status', 1);
        })->where([
            ['company_code', Auth::user()->company_roles->first()->company->id],
            ['Status', '1'],
        ])->orderBy('pName', 'asc')->get();

        $employees = DB::table('time_keepers')
            ->select(DB::raw(
                'e.*,
                sum(time_keepers.duration) as total_hours,
                sum(time_keepers.amount) as total_amount'
            ))
            ->leftJoin('employees as e', 'e.id', 'time_keepers.employee_id')
            ->where([
                ['e.company', Auth::user()->company_roles->first()->company->id],
                ['e.role', 3],
                $filter_project,
                $filter_roster_status,
                // $filter_roster_type,
                ['roaster_type', 'Schedueled']
            ])
            ->groupBy("e.id")
            ->orderBy('e.fname')
            ->whereBetween('roaster_date', [$start_date, $end_date])
            ->get();

        $data = [];
        if ($employees->count() > 0) {
            foreach ($employees as $key => $employee) {
                $mon_ = [];
                $tue_ = [];
                $wed_ = [];
                $thu_ = [];
                $fri_ = [];
                $sat_ = [];
                $sun_ = [];

                $timekeepers = TimeKeeper::where([
                    ['employee_id', $employee->id],
                    ['company_code', Auth::user()->company_roles->first()->company->id],
                    ['roaster_type', 'Schedueled'],
                    // $filter_roster_type,
                    $filter_roster_status,
                    $filter_project
                ])->whereBetween('roaster_date', [$start_date, $end_date])
                ->with(['roaster_status'])
                    ->get();
                    
            
    

                foreach (AdminRosterResource::collection($timekeepers) as $timekeeper) {
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
                }

                array_push($data, [
                    'employee' => $employee->fname . ' ' . $employee->mname . ' ' . $employee->lname,
                    'image' => $employee->image ? asset($employee->image) : "",
                    'total_hours' => $employee->total_hours,
                    'weeks' => [
                        [
                            "day" => "Monday",
                            "shifts" => $mon_
                        ],
                        [
                            "day" => "Tuesday",
                            "shifts" => $tue_
                        ],
                        [
                            "day" => "Wednesday",
                            "shifts" => $wed_
                        ],
                        [
                            "day" => "Thursday",
                            "shifts" => $thu_
                        ],
                        [
                            "day" => "Friday",
                            "shifts" => $fri_
                        ],
                        [
                            "day" => "Saturday",
                            "shifts" => $sat_
                        ],
                        [
                            "day" => "Sunday ",
                            "shifts" => $sun_
                        ],
                    ],
                ]);
            }

            // $project  = $employees ? $timekeeper->project->pName: '';

            return send_response(true, '', [
                'week' => $start_date->format('d M, Y') . ' -  ' . $end_date->format('d M, Y'),
                'total_hours' => (string) $employees->sum('total_hours'),
                'total_amount' => (string) round($employees->sum('total_amount'), 2),
                'projects' => $projects,
                'data' => $data,
            ]);
        }

        return send_response(true, '', [
            'week' => $start_date->format('d M, Y') . ' -  ' . $end_date->format('d M, Y'),
            'total_hours' => (string) 0,
            'total_amount' => (string) 0,
            'projects' => $projects,
            'data' => [],
        ]);
    }
    
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
                ['roaster_status_id', roaster_status('Accepted')],
                ['roaster_type', 'Schedueled']
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

        $projects = Project::where([
            ['company_code', auth()->user()->company_roles->first()->company->id],
            ['status', 1]
        ])->get();
        $filter_project = $request->project ? ['project_id', $request->project] : ['project_id', '>', 0];

        $start_date = Carbon::parse($week)->startOfWeek();
        $end_date = Carbon::parse($week)->endOfWeek();

        $mon_ = [];
        $tue_ = [];
        $wed_ = [];
        $thu_ = [];
        $fri_ = [];
        $sat_ = [];
        $sun_ = [];
        
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

        if (empty($request->project)) {
            return send_response(true, '', [
                'week' => $start_date->format('d M, Y') . ' -  ' . $end_date->format('d M, Y'),
                "current_project" => $request->project?(int)$request->project:null,
                "projects" => UserProjectResource::collection($projects),
                "job_type" => [],
                'data' => [],
            ]);
        }

        return send_response(true, '', [
            'week' => $start_date->format('d M, Y') . ' -  ' . $end_date->format('d M, Y'),
            "current_project" => $request->project?(int)$request->project:null,
            "projects" => UserProjectResource::collection($projects),
            "job_type" => $job_type,
            'data' => [
                [
                    "day" => "Monday",
                    "shifts" => ScheduledCalendarResource::collection($mon_)
                ],
                [
                    "day" => "Tuesday",
                    "shifts" => ScheduledCalendarResource::collection($tue_)
                ],
                [
                    "day" => "Wednesday",
                    "shifts" => ScheduledCalendarResource::collection($wed_)
                ],
                [
                    "day" => "Thursday",
                    "shifts" => ScheduledCalendarResource::collection($thu_)
                ],
                [
                    "day" => "Friday",
                    "shifts" => ScheduledCalendarResource::collection($fri_)
                ],
                [
                    "day" => "Saturday",
                    "shifts" => ScheduledCalendarResource::collection($sat_)
                ],
                [
                    "day" => "Sunday ",
                    "shifts" => ScheduledCalendarResource::collection($sun_)
                ],
            ]
        ]);
    }

    public function publish(Request $request)
    {
        set_time_limit(300);

        $copy_week = Carbon::parse($request->week);
        $start_date = Carbon::parse($copy_week)->startOfWeek();
        $end_date = Carbon::parse($copy_week)->endOfWeek();
        $filter_project = $request->project ? ['project_id', $request->project] : ['employee_id', '>', 0];

        $employees = DB::table('time_keepers')
            ->select(DB::raw(
                'e.* ,
                e.fname as name'
            ))
            ->leftJoin('employees as e', 'e.id', 'time_keepers.employee_id')
            ->where([
                ['e.company', Auth::user()->company_roles->first()->company->id],
                ['e.role', 3],
                ['roaster_type', 'Schedueled'],
                $filter_project
            ])
            ->orderBy("e.fname")
            ->whereBetween('roaster_date', [$start_date, $end_date])
            ->get();

        if ($employees) {
            foreach ($employees as $employee) {
                $timekeepers = TimeKeeper::where([
                    ['company_code', Auth::user()->company_roles->first()->company->id],
                    ['employee_id', $employee->id],
                    ['roaster_type', 'Schedueled'],
                    ['shift_end','>',Carbon::now()],
                    ['roaster_status_id', roaster_status('Not published')],
                    $filter_project
                ])
                    ->whereBetween('roaster_date', [$start_date, $end_date])
                    ->get();
                TimeKeeper::where([
                    ['company_code', Auth::user()->company_roles->first()->company->id],
                    ['employee_id', $employee->id],
                    ['roaster_type', 'Schedueled'],
                    ['shift_end','>',Carbon::now()],
                    ['roaster_status_id', roaster_status('Not published')],
                    $filter_project
                ])
                    ->whereBetween('roaster_date', [$start_date, $end_date])
                    ->update(['roaster_status_id' => roaster_status('Published')]);

                if ($timekeepers->count()) {
                    $shift = $timekeepers->first();
                    if ($timekeepers->count() == 1) {
                        $msg = 'There is a shift at ' . $shift->project->pName . ' for week ending ' . Carbon::parse($shift->roaster_date)->endOfWeek()->format('d-m-Y');
                    } else {
                        $msg = 'There have shifts at ' . $shift->project->pName . ' for week ending ' . Carbon::parse($shift->roaster_date)->endOfWeek()->format('d-m-Y');
                    }

                    $shift->employee->user->notify(new NewShiftNotification($msg,$shift));
                    push_notify('Shift Alert :', $msg . ' Please log on to eazytask to accept / declined it.', $shift->employee->employee_role, $shift->employee->firebase, 'unconfirmed-shift');
                }
            }
        }

        return send_response(true, 'week successfully published');
    }

    public function copy_week(Request $request)
    {
        set_time_limit(300);
        $copy_week = Carbon::parse($request->week);
        $start_date = Carbon::parse($copy_week)->startOfWeek();
        $end_date = Carbon::parse($copy_week)->endOfWeek();

        $filter_project = $request->project ? ['project_id', $request->project] : ['employee_id', '>', 0];
        $timekeepers = TimeKeeper::where([
            ['company_code', Auth::user()->company_roles->first()->company->id],
            ['roaster_type', 'Schedueled'],
            $filter_project
        ])->whereBetween('roaster_date', [$start_date, $end_date])
            ->get();

        foreach ($timekeepers as $timekeeper) {
            $roster = new TimeKeeper;
            $roster->roaster_date = Carbon::parse($timekeeper->roaster_date)->addWeeks();
            $roster->shift_start = Carbon::parse($timekeeper->shift_start)->addWeeks();
            $roster->shift_end = Carbon::parse($timekeeper->shift_end)->addWeeks();
            $roster->sing_in = null;
            $roster->sing_out = null;
            $roster->payment_status = 0;

            $roster->user_id = Auth::id();
            $roster->employee_id = $timekeeper->employee_id;
            $roster->client_id = $timekeeper->client_id;
            $roster->project_id = $timekeeper->project_id;
            $roster->company_id = $timekeeper->company_id;
            $roster->company_code = Auth::user()->company_roles->first()->company->id;
            $roster->duration = $timekeeper->duration;
            $roster->ratePerHour = $timekeeper->ratePerHour;
            $roster->amount = $timekeeper->amount;
            $roster->job_type_id = $timekeeper->job_type_id;
            // $roster->roaster_id = Auth::id();
            $roster->roaster_status_id = roaster_status("Not published");

            $roster->roaster_type = $timekeeper->roaster_type;

            $roster->remarks = $timekeeper->remarks;
            $roster->created_at = Carbon::now();
            $roster->save();
        }
        return send_response(true, 'week successfully copied');
    }

    public function drop_roster(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'timekeeper_id' => 'required',
            'employee_id' => 'required',
            'change_date' => 'required',
        ]);
        if ($validator->fails())
            return send_response(false, 'validation error!', $validator->errors(), 400);

        try {
            $timekeeper = TimeKeeper::find($request->timekeeper_id);
            $change_date = Carbon::parse($request->change_date);
            $diff = Carbon::parse($timekeeper->roaster_date)->diffInDays($change_date, false);

            if ($timekeeper->roaster_type == 'Unschedueled' && $change_date > Carbon::now()) {
                return send_response(false, 'validation error!', ['change_date' => "advance date not support for unschedule"], 400);
            }
            $timekeeper->employee_id = $request->employee_id;
            $timekeeper->roaster_status_id = roaster_status('Not published');
            $timekeeper->roaster_date = Carbon::parse($timekeeper->roaster_date)->addDay($diff);
            $timekeeper->shift_start = Carbon::parse($timekeeper->shift_start)->addDay($diff);
            $timekeeper->shift_end = Carbon::parse($timekeeper->shift_end)->addDay($diff);
            $timekeeper->save();

            return send_response(true, "successfully changed.");
        } catch (\Throwable $e) {
            return send_response(false, $e->getMessage(), [], 400);
        }
    }

    public function sign_in_status(Request $request)
    {
        $projects = Project::where([
            ['company_code', auth()->user()->company_roles->first()->company->id],
            ['status', 1]
        ])->get();

        $week = Carbon::parse($request->week);
        $filter_project = $request->project ? ['project_id', $request->project] : ['employee_id', '>', 0];

        $start_date = Carbon::parse($week)->startOfWeek();
        $end_date = Carbon::parse($week)->endOfWeek();

        $mon_ = [];
        $tue_ = [];
        $wed_ = [];
        $thu_ = [];
        $fri_ = [];
        $sat_ = [];
        $sun_ = [];

        $timekeepers = TimeKeeper::where([
            // ['employee_id', $employee->id],
            ['company_code', Auth::user()->company_roles->first()->company->id],
            ['roaster_type', 'Schedueled'],
            ['roaster_status_id', roaster_status('Accepted')],
            // $filter_roster_type,
            $filter_project
        ])->whereBetween('roaster_date', [$start_date, $end_date])
            // ->where(function ($q) {
            //     avoid_rejected_key($q);
            // })
            ->get();

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
        }

        return send_response(true, '', [
            'week' => $start_date->format('d M, Y') . ' -  ' . $end_date->format('d M, Y'),
            "current_project" => $request->project,
            "projects" => $projects,
            'data' => [
                [
                    "day" => "Monday",
                    "shifts" => $mon_
                ],
                [
                    "day" => "Tuesday",
                    "shifts" => $tue_
                ],
                [
                    "day" => "Wednesday",
                    "shifts" => $wed_
                ],
                [
                    "day" => "Thursday",
                    "shifts" => $thu_
                ],
                [
                    "day" => "Friday",
                    "shifts" => $fri_
                ],
                [
                    "day" => "Saturday",
                    "shifts" => $sat_
                ],
                [
                    "day" => "Sunday ",
                    "shifts" => $sun_
                ],
            ]
        ]);
    }

    public function approve_week(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'week' => 'required',
        ]);
        if ($validator->fails())
            return send_response(false, 'validation error!', $validator->errors(), 400);

        $copy_week = Carbon::parse($request->week);
        $start_date = Carbon::parse($copy_week)->startOfWeek();
        $end_date = Carbon::parse($copy_week)->endOfWeek();
        $filter_project = $request->project ? ['project_id', $request->project] : ['employee_id', '>', 0];

        DB::table('time_keepers')
            ->where([
                ['company_code', Auth::user()->company_roles->first()->company->id],
                ['shift_end', '<=', Carbon::now()],
                $filter_project
            ])
            ->whereNotNull('sing_in')
            ->where(function ($q) {
                $q->where('roaster_type', 'Schedueled');
                $q->where('roaster_status_id', roaster_status('Accepted'));
                $q->orWhere(function ($q) {
                    $q->where('roaster_type', 'Unschedueled');
                });
            })
            ->whereBetween('roaster_date', [$start_date, $end_date])
            ->update(array('is_approved' => 1));

        return send_response(true, 'week successfully approved');
    }
}
