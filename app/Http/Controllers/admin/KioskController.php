<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\user\UserActivityPhotoController;
use App\Http\Resources\admin\EmployeeResource;
use App\Http\Resources\user\UserTimekeeperResource;
use App\Jobs\AutoSignOutJob;
use App\Models\Employee;
use App\Models\Inductedsite;
use App\Models\Project;
use App\Models\TimeKeeper;
use App\Models\User;
use App\Models\UserActivityPhoto;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class KioskController extends Controller
{
    public function fetch_employees(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required',
            'employee_filter' => 'required',
        ]);
        if ($validator->fails())
            return send_response(false, 'validation error!', $validator->errors(), 400);

        $project = Project::find($request->project_id);
        if ($project) {
            if ($request->employee_filter == 'shift') {
                // return $request->all();
                $employees = TimeKeeper::select('e.*', 'time_keepers.*', 'e.id as id', 'time_keepers.id as time_keeper_id')
                    ->leftJoin('employees as e', 'e.id', 'time_keepers.employee_id')
                    ->where([
                        ['e.company', Auth::user()->company_roles->first()->company->id],
                        ['e.role', 3],
                    ])
                    ->where(function ($q) {
                        e_avoid_expired_license($q);
                    })
                    ->groupBy("e.id")
                    ->orderBy('e.fname', 'asc')
                    ->where([
                        ['company_code', Auth::user()->company_roles->first()->company->id],
                        ['roaster_date', Carbon::now()->toDateString()],
                        ['project_id', $project->id],
                        ['roaster_status_id', roaster_status('Accepted')]
                    ])->with('shiftDetails')
                    ->get();
            }
            // elseif($request->employee_filter == 'shift_details'){
            //     $employees_query = Employee::where('employees.company', Auth::user()->company_roles->first()->company->id)
            //     ->with('shiftDetails');
            //     if($request->employee_id){
            //         $employees_query->where('employees.id', $request->employee_id);
            //     }
            //     $employees = $employees_query->get();
            //     return send_response(true, 'Employees fetched successfully', $employees, 200);
            // }
            elseif ($request->employee_filter == 'inducted') {
                $employees = Inductedsite::select(DB::raw(
                        'e.*'
                ))
                    ->leftJoin('employees as e', 'e.id', 'inductedsites.employee_id')
                    ->where([
                        ['e.company', Auth::user()->company_roles->first()->company->id],
                        ['e.role', 3],
                        ['e.status', 1]
                    ])
                    ->where(function ($q) {
                        e_avoid_expired_license($q);
                    })
                    ->groupBy("e.id")
                    ->orderBy('e.fname', 'asc')
                    ->where([
                        ['company_code', Auth::user()->company_roles->first()->company->id],
                        ['project_id', $project->id],
                    ])->with('shiftDetails')
                    ->get();
            } else {
                $employees = Employee::where([
                    ['company', Auth::user()->company_roles->first()->company->id],
                    ['role', 3],
                    ['status', 1]
                ])
                ->where(function ($q) {
                    avoid_expired_license($q);
                })->with('shiftDetails')
                ->orderBy('fname', 'asc')
                ->get();
            }

            return send_response(true, '', EmployeeResource::collection($employees));
        } else {
            return send_response(false, 'invalid project!', [], 400);
        }
    }

    public function check_pin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required',
            'project_id' => 'required',
            'pin' => 'required',
        ]);
        if ($validator->fails())
            return send_response(false, 'validation error!', $validator->errors(), 400);

        $employee = Employee::find($request->employee_id);
        if ($employee) {
            $user = User::find($employee->userID);
            if ($user->pin) {
                if ($user->pin == $request->pin) {
                    $roaster = TimeKeeper::where([
                        ['employee_id', $request->employee_id],
                        ['project_id', $request->project_id],
                        ['company_code', Auth::user()->company_roles->first()->company->id],
                        // ['roaster_date', Carbon::now()->toDateString()],
                        ['sing_out', null],
                        // ['shift_end', '>', Carbon::now()->subHour()],
                        ['roaster_status_id', roaster_status('Accepted')]
                    ])->where(function ($q) {
                        $q->where('sing_in', '!=', null);
                        $q->orWhere(function ($q) {
                            $q->where('shift_end', '>', Carbon::now());
                        });
                    })->where(function ($q) {
                        $q->where('roaster_date', Carbon::now()->format("Y-m-d"));
                        // $q->orWhere(function ($q) {
                        //     $q->where('roaster_date', Carbon::now()->subDay()->format("Y-m-d"));
                        //     $q->where('shift_end', '>', Carbon::now()->format("Y-m-d"));
                        // });
                    })
                        ->orderBy('shift_start', 'asc')
                        ->get();

                    $final_roaster = null;
                    if (count($roaster) > 1) {
                        foreach($roaster as $index => $item) {
                            if($item->sing_in != null) {
                                $final_roaster = $item;
                            }
                            if($item->shift_end > Carbon::now() && $item->sing_in == null && $item->shift_start <= Carbon::now()->addMinutes(15)) {
                                if(!empty($roaster[$index-1])) {
                                    if($roaster[$index-1]->sing_out == null) {
                                        $roaster[$index-1]->sing_out = $roaster[$index-1]->shift_end;
                                        $roaster[$index-1]->save();
                                    }
                                }
                                $final_roaster = $item;
                                break;
                            }
                        }
                    }else{
                        $final_roaster = $roaster[0] ?? null;
                    }
                    
                    $data = null;
            
                    if ($final_roaster) {
                        $data = new UserTimekeeperResource($final_roaster);
                    }

                    return send_response(true, '', $data);
                } else {
                    return send_response(false, "wrong pin!", [], 400);
                }
            } else {
                return send_response(false, "you didn't set any pin!", [], 400);
            }
        }
        return send_response(false, 'invalid user!', [], 400);
    }

    public function employee_shift(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required',
            'project_id' => 'required'
        ]);
        if ($validator->fails())
            return send_response(false, 'validation error!', $validator->errors(), 400);

        $roaster = TimeKeeper::where([
            ['employee_id', $request->employee_id],
            ['project_id', $request->project_id],
            ['company_code', Auth::user()->company_roles->first()->company->id],
            ['sing_out', null],
            ['roaster_status_id', roaster_status('Accepted')]
        ])->where(function ($q) {
            $q->where('sing_in', '!=', null);
            $q->orWhere(function ($q) {
                $q->where('shift_end', '>', Carbon::now());
            });
        })->where(function ($q) {
            $q->where('roaster_date', Carbon::now()->format("Y-m-d"));
            $q->orWhere(function ($q) {
                $q->where('roaster_date', Carbon::now()->subDay()->format("Y-m-d"));
                $q->where('shift_end', '>', Carbon::now()->format("Y-m-d"));
            });
        })
            ->orderBy('shift_start', 'asc')
            ->first();

        return send_response(true, '',$roaster? new UserTimekeeperResource($roaster):[]);
    }

    public function storeTimekeeper(Request $request)
    {
        if(!is_active_employee($request->employee_id)){
            return send_response(false, "this is an inactive employee!", [], 400);
        }

        $shift = TimeKeeper::where([
            ['company_code', Auth::user()->company_roles->first()->company->id],
            ['employee_id', $request->employee_id],
            ['sing_out', null],
        ])
            ->where(function ($q) {
                $q->where('sing_in', '!=', null);
                $q->orWhere(function ($q) {
                    $q->where('shift_start', '<', Carbon::now());
                    $q->where('shift_end', '>', Carbon::now());
                });
            })
            ->where(function ($q) {
                $q->where('roaster_date', Carbon::now()->format("Y-m-d"));
                $q->orWhere(function ($q) {
                    $q->where('roaster_date', Carbon::now()->subDay()->format("Y-m-d"));
                    $q->where('shift_end', '>', Carbon::now()->format("Y-m-d"));
                });
            })
            ->first();
        if ($shift)
            return send_response(false, "you're already signed in ".$shift->project->pName, [], 400);

        $validator = Validator::make($request->all(), [
            'employee_id' => 'required',
            'project_id' => 'required',
            'ratePerHour' => 'required',
            'job_type_id' => 'required',
        ]);
        if ($validator->fails())
            return send_response(false, 'validation error!', $validator->errors(), 400);

        $project = Project::find($request->project_id);

        $duration = 4;
        $shift_start = Carbon::now();
        $shift_end = Carbon::now()->addHours(4);

        $timekeeper = new TimeKeeper();
        $timekeeper->user_id = Auth::id();
        $timekeeper->employee_id = $request->employee_id;
        $timekeeper->client_id = $project->clientName;
        $timekeeper->project_id = $request->project_id;
        $timekeeper->company_id = Auth::user()->company_roles->first()->company->id;
        $timekeeper->roaster_date = Carbon::today()->toDateString();
        $timekeeper->shift_start = $shift_start;
        $timekeeper->shift_end = $shift_end;

        $timekeeper->sing_in = $shift_start;

        $timekeeper->company_code = Auth::user()->company_roles->first()->company->id;
        $timekeeper->duration = $duration;
        $timekeeper->ratePerHour = $request->ratePerHour;
        $timekeeper->amount = $duration * $request->ratePerHour;
        $timekeeper->job_type_id = $request->job_type_id;
        $timekeeper->roaster_status_id = roaster_status('Accepted');
        $timekeeper->roaster_type = 'Unschedueled';
        $timekeeper->remarks = $request->remarks;
        $timekeeper->signin_comment = $request->comment ?? null;
        $timekeeper->created_at = Carbon::now();
        $timekeeper->save();

        AutoSignOutJob::dispatch($timekeeper->id)->delay(now()->addHours(6));

        if ($request->lat && $request->lon) {
            $user_activity = new UserActivityPhoto();
            $user_activity->lat  = $request->lat;
            $user_activity->lon  = $request->lon;
            $user_activity->timekeeper_id  = $timekeeper->id;
            $user_activity->save();
        }
        if ($request->image) {
            $user_activity = new UserActivityPhotoController;
            $user_activity->store($request->image, $timekeeper->id);
        }

        return send_response(true, 'roster Successfully Added.', new UserTimekeeperResource($timekeeper));
    }

    public function signIn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'timekeeper_id' => 'required',
        ]);
        if ($validator->fails())
            return send_response(false, 'validation error!', $validator->errors(), 400);

        try {
            $roster = TimeKeeper::find($request->timekeeper_id);
            $roster->sing_in = Carbon::now();
            $roster->signin_comment = $request->comment ?? null;
            $roster->save();

            if ($request->lat && $request->lon) {
                $user_activity = new UserActivityPhoto();
                $user_activity->lat  = $request->lat;
                $user_activity->lon  = $request->lon;
                $user_activity->timekeeper_id  = $roster->id;
                $user_activity->save();
            }
            if ($request->image) {
                $user_activity = new UserActivityPhotoController;
                $user_activity->store($request->image, $roster->id);
            }
            AutoSignOutJob::dispatch($roster->id)->delay(now()->addHours(6));
            return send_response(true, 'Successfully sign in');
        } catch (\Throwable $e) {
            return send_response(false, $e->getMessage(), [], 400);
        }
    }
    public function signOut(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'timekeeper_id' => 'required',
        ]);
        if ($validator->fails())
            return send_response(false, 'validation error!', $validator->errors(), 400);

        try {
            $roster = TimeKeeper::find($request->timekeeper_id);

            if (!$roster->sing_out) {
                $roster->sing_out = Carbon::now();
                if ($roster->roaster_type == 'Unschedueled') {
                    $now = Carbon::now();
                    $total_hour = $now->floatDiffInRealHours($roster->sing_in);

                    $roster->shift_end = $now;
                    $roster->duration = round($total_hour, 2);
                    $roster->amount = round($total_hour * $roster->ratePerHour);

                    $roster->Approved_end_datetime = $now;
                    $roster->app_duration = round($total_hour, 2);
                    $roster->app_amount = round($total_hour * $roster->ratePerHour);
                }
            }
            $roster->disableLogging();
            $roster->signout_comment = $request->comment ?? null;
            $roster->save();

            if ($request->image) {
                $user_activity = new UserActivityPhotoController;
                $user_activity->store($request->image, $request->timekeeper_id, 'sign_out');
            }

            return send_response(true, 'Successfully sign out');
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
    }
}
