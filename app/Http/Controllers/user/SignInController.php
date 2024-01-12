<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\TimeKeeper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\user\UserActivityPhotoController;
use App\Http\Resources\admin\ScheduledCalendarResource;
use App\Http\Resources\user\UserTimekeeperResource;
use App\Jobs\AutoSignOutJob;
use App\Jobs\FirebaseShiftNotificationJob;
use App\Models\UserActivityPhoto;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class SignInController extends Controller
{
    public function index()
    {
        $roaster = TimeKeeper::where([
            ['employee_id', Auth::user()->employee->id],
            ['company_code', Auth::user()->company_roles->first()->company->id],
            ['sing_out', null],
        ])->where(function ($q) {
            $q->where('roaster_type','Schedueled');
            $q->where('roaster_status_id',roaster_status('Accepted'));
            $q->orWhere(function ($q) {
                $q->where('roaster_type','Unschedueled');
                $q->where('sing_in', '!=', null);
            });
        })->where(function ($q) {
            $q->where(function ($q) {
                $q->where('sing_in', '!=', null)
                    ->orWhere('shift_end', '>', Carbon::now());
            });
            // $q->where('sing_in', '!=', null);
            // $q->orWhere(function ($q) {
            //     $q->where('shift_end', '>', Carbon::now());
            // });

            // $q->where('shift_end', '>', Carbon::now());
            // $q->orWhere('sing_in', '!=', null);
        })->where(function ($q) {
            $q->where('roaster_date', Carbon::now()->format("Y-m-d"));
            
            // $twoDaysAgo = Carbon::now()->subDays(2)->format("Y-m-d");
            
            // $today = Carbon::now()->format("Y-m-d");
            
            // $q->whereBetween('roaster_date', [$twoDaysAgo, $today]);

            // $q->orWhere(function ($q) {
            //     $q->where('roaster_date', Carbon::now()->subDay()->format("Y-m-d"));
                // $q->where('shift_end', '>', Carbon::now()->format("Y-m-d"));
            // });
        })
        ->orderBy('shift_start', 'asc')->get();
        
        $final_roaster = null;dd($roaster);
        if (count($roaster) > 1) {
            foreach($roaster as $item) {
                if($item->shift_end > Carbon::now() && $item->sign_in != null) {
                    $final_roaster = $item;
                    break;
                }
                if($item->shift_end > Carbon::now() && $item->sing_in == null) {
                    $final_roaster = $item;
                    break;
                }
            }
        }else{
            $final_roaster = $roaster[0] ?? null;
        }
        
        $data = [];

        if ($final_roaster) {
            $data[0] = new UserTimekeeperResource($final_roaster);
        }
        return send_response(true, '', $data);
    }

    public function signIn(Request $request)
    {
        if ($this->check_license()) {
            return $this->check_license();
        }

        $validator = Validator::make($request->all(), [
            'timekeeper_id' => 'required',
        ]);
        if ($validator->fails())
            return send_response(false, 'validation error!', $validator->errors(), 400);

        try {
            // TimeKeeper::where('id', '!=', $request->timekeeper_id)
            // ->where([
            //     ['employee_id', Auth::user()->employee->id],
            //     ['company_code', Auth::user()->company_roles->first()->company->id],
            //     ['sing_out', null],
            //     ['sing_in', '!=', null],
            // ])->where(function ($q) {
            //     $q->where('roaster_type','Schedueled');
            //     $q->where('roaster_status_id',roaster_status('Accepted'));
            //     $q->orWhere(function ($q) {
            //         $q->where('roaster_type','Unschedueled');
            //         $q->where('sing_in', '!=', null);
            //     });
            // })->where(function ($q) {
            //     // $q->where('sing_in', '!=', null);
            //     // $q->orWhere(function ($q) {
            //     //     $q->where('shift_end', '>', Carbon::now());
            //     // });
            // })->where(function ($q) {
            //     // $q->where('roaster_date', Carbon::now()->format("Y-m-d"));
                
            //     // $twoDaysAgo = Carbon::now()->subDays(2)->format("Y-m-d");
                
            //     // $today = Carbon::now()->format("Y-m-d");
                
            //     // $q->whereBetween('roaster_date', [$twoDaysAgo, $today]);
    
            //     // $q->orWhere(function ($q) {
            //     //     $q->where('roaster_date', Carbon::now()->subDay()->format("Y-m-d"));
            //         // $q->where('shift_end', '>', Carbon::now()->format("Y-m-d"));
            //     // });
            // })
            //     ->orderBy('shift_start', 'asc')
            //     ->update([
            //         'sing_out' => Carbon::now()
            //     ]);

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
            FirebaseShiftNotificationJob::dispatch($roster->employee->firebase, $roster->id)->delay(Carbon::parse($roster->shift_end));
            FirebaseShiftNotificationJob::dispatch($roster->employee->firebase, $roster->id)->delay(Carbon::parse($roster->shift_end)->addMinutes(15));


            return send_response(true, 'Successfully sign in');
        } catch (\Throwable $e) {
            return $e->getMessage();
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
            $this->addSignOut($request->timekeeper_id);
            if ($request->image) {
                $user_activity = new UserActivityPhotoController;
                $user_activity->store($request->image, $request->timekeeper_id, 'sign_out');
            }
            return send_response(true, 'Successfully sign out');
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
    }

    public function storeTimekeeper(Request $request)
    {
        if ($this->check_license()) {
            return $this->check_license();
        }

        $shift = TimeKeeper::where([
            ['company_code', Auth::user()->employee->company],
            ['employee_id', Auth::user()->employee->id],
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
        if ($shift) {
            return send_response(false, "you're already signed in ".$shift->project->pName, [], 400);
        }

        try {
            $validator = Validator::make($request->all(), [
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
            $timekeeper->user_id = Auth::user()->employee->user_id;
            $timekeeper->employee_id = Auth::user()->employee->id;
            $timekeeper->client_id = $project->clientName;
            $timekeeper->project_id = $request->project_id;
            $timekeeper->company_id = Auth::user()->employee->company;
            $timekeeper->roaster_date = Carbon::today()->toDateString();
            $timekeeper->shift_start = $shift_start;
            $timekeeper->shift_end = $shift_end;

            $timekeeper->sing_in = $shift_start;

            $timekeeper->company_code = Auth::user()->employee->company;
            $timekeeper->duration = $duration;
            $timekeeper->ratePerHour = $request->ratePerHour;
            $timekeeper->amount = $duration * $request->ratePerHour;
            $timekeeper->job_type_id = $request->job_type_id;
            $timekeeper->roaster_status_id = roaster_status('Accepted');
            $timekeeper->roaster_type = 'Unschedueled';
            $timekeeper->remarks = $request->remarks;
            $timekeeper->signin_comment = $request->signin_comment ?? null;
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

            $timekeeper = TimeKeeper::find($timekeeper->id);
            return send_response(true, 'roster Successfully Added.', new UserTimekeeperResource($timekeeper));
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
    }

    public function addSignOut($timekeeper_id, $auto = false)
    {
        Log::alert($timekeeper_id);

        $roster = TimeKeeper::find($timekeeper_id);

        if (!$roster->sing_out) {
            $roster->sing_out = $auto ? $roster->shift_end : Carbon::now();
            $roster->signout_comment = request()->comment ?? null;

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
        $roster->save();
    }

    private function check_license()
    {
        $emp = Auth::user()->employee;
        if ($emp->license_expire_date < Carbon::now()->toDateString() || $emp->first_aid_expire_date < Carbon::now()->toDateString()) {
            return send_response(false, 'sorry! your license is expired.', [], 400);
        } elseif ($comp = $emp->compliances->where('expire_date', '<', Carbon::now()->toDateString())->first()) {
            return send_response(false, "sorry! your compliance " . $comp->compliance->name . " is expired.", [], 400);
        } else {
            return false;
        }
    }
}
