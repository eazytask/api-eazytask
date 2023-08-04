<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use App\Http\Resources\user\UserTimekeeperResource;
use App\Models\Project;
use App\Models\TimeKeeper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TimesheetController extends Controller
{
    public function index(Request $request)
    {
        $start_date = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->startOfYear();
        $end_date = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now()->endOfYear();
        $filter_project = $request->project_id ? ['project_id', $request->project_id] : ['employee_id', '>', 0];
        $timekeepers = TimeKeeper::where([
            $filter_project,
            ['employee_id', Auth::user()->employee->id],
            ['company_code', Auth::user()->employee->company],
            ['payment_status', 0],
            ['roaster_type', 'Unschedueled']
        ])
            ->where(function ($q) {
                avoid_rejected_key($q);
            })
            // ->with('project', 'roaster_status', 'job_type')
            ->whereBetween('roaster_date', [$start_date->toDateString(), $end_date->toDateString()])
            ->limit(20)
            ->orderBy('roaster_date', 'desc')
            ->get();

        return send_response(true, '', UserTimekeeperResource::collection($timekeepers));
    }

    public function store(Request $request)
    {
        if ($this->check_license()) {
            return $this->check_license();
        }
        $validator = Validator::make($request->all(), [
            'roaster_date' => 'required',
            'shift_start' => 'required',
            'duration' => 'required',
            'ratePerHour' => 'required',
            'amount' => 'required',
            'project_id' => 'required',
            'job_type_id' => 'required',
        ]);
        if ($validator->fails())
            return send_response(false, 'validation error!', $validator->errors(), 400);
        try {
            $project = Project::find($request->project_id);
            $shift_start = Carbon::parse($request->roaster_date . $request->shift_start);
            $shift_end = Carbon::parse($shift_start)->addMinute($request->duration * 60);

            $total_hour = $shift_start->floatDiffInRealHours($shift_end);
            $duration = round($total_hour, 2);
            $rate = $request->ratePerHour;
            $errors = [];
            if (round($duration * $rate, 2) != $request->amount) {
                $errors['amount'] = 'amount is not correct!';
            }
            if (count($errors)) {
                return send_response(false, 'validation error!', $errors, 400);
            }

            $timekeeper = new TimeKeeper();

            if (Carbon::parse($request->roaster_date) > Carbon::now()) {
                return send_response(false, 'validation error!', ['roaster_date' => "advance date not support for unschedule"], 400);
            } else {
                $timekeeper->roaster_type = 'Unschedueled';
            }

            $timekeeper->user_id =  Auth::user()->employee->user_id;
            $timekeeper->employee_id = Auth::user()->employee->id;
            $timekeeper->client_id = $project->clientName;
            $timekeeper->project_id = $request->project_id;
            $timekeeper->company_id = Auth::user()->employee->user_id;
            $timekeeper->roaster_date = Carbon::parse($request->roaster_date);
            $timekeeper->shift_start = $shift_start;
            $timekeeper->shift_end = $shift_end;
            // $timekeeper->sing_in = $shift_start;
            // $timekeeper->sing_out = $shift_end;
            $timekeeper->company_code = Auth::user()->employee->company;
            $timekeeper->duration = $duration;
            $timekeeper->ratePerHour = $rate;
            $timekeeper->amount = $duration * $rate;
            $timekeeper->job_type_id = $request->job_type_id;
            // $timekeeper->roaster_id = Auth::id();
            $timekeeper->roaster_status_id = roaster_status('Accepted');
            $timekeeper->remarks = $request->remarks;
            $timekeeper->created_at = Carbon::now();
            $timekeeper->save();
$timekeeper = TimeKeeper::find($timekeeper->id);
            return send_response(true, 'Roster Successfully Added.', new UserTimekeeperResource($timekeeper));
        } catch (\Throwable $e) {
            return send_response(false, $e->getMessage(), '', 422);
        }
    }

    public function update(Request $request)
    {
        if ($this->check_license()) {
            return $this->check_license();
        }
        $validator = Validator::make($request->all(), [
            'timekeeper_id' => 'required',
            'roaster_date' => 'required',
            'shift_start' => 'required',
            'duration' => 'required',
            'ratePerHour' => 'required',
            'amount' => 'required',
            'project_id' => 'required',
            'job_type_id' => 'required',
        ]);
        if ($validator->fails())
            return send_response(false, 'validation error!', $validator->errors(), 400);
        try {
            $project = Project::find($request->project_id);

            $shift_start = Carbon::parse($request->roaster_date . $request->shift_start);
            $shift_end = Carbon::parse($request->roaster_date . $request->shift_end);

            $total_hour = $shift_start->floatDiffInRealHours($shift_end);
            $duration = round($total_hour, 2);
            $rate = $request->ratePerHour;
            $errors = [];
            if ($duration != $request->duration) {
                $errors['duration'] = 'duration is not correct!';
            }
            if (round($duration * $rate, 2) != $request->amount) {
                $errors['amount'] = 'amount is not correct!';
            }
            if (count($errors)) {
                return send_response(false, 'validation error!', $errors, 400);
            }

            $timekeeper = TimeKeeper::find($request->timekeeper_id);
            
            if (Carbon::parse($request->roaster_date) > Carbon::now()) {
                return send_response(false, 'validation error!', ['roaster_date' => "advance date not support for unschedule"], 400);
            }

            $timekeeper->client_id = $project->clientName;
            $timekeeper->project_id = $request->project_id;
            $timekeeper->roaster_date = Carbon::parse($request->roaster_date);
            $timekeeper->shift_start = $shift_start;
            $timekeeper->shift_end = $shift_end;

            $timekeeper->duration = $duration;
            $timekeeper->ratePerHour = $rate;
            $timekeeper->amount = $duration * $rate;
            $timekeeper->job_type_id = $request->job_type_id;
            // $timekeeper->roaster_id = Auth::id();
            $timekeeper->remarks = $request->remarks;
            // return $timekeeper;
            $timekeeper->save();
            return send_response(true, 'Roster successfully updated.', new UserTimekeeperResource($timekeeper));
        } catch (\Throwable $e) {
            return send_response(false, $e->getMessage(), '', 422);
        }
    }

    public function delete($id)
    {
        if ($this->check_license()) {
            return $this->check_license();
        }

        try {
            $timekeeper = TimeKeeper::find($id);
            $timekeeper->delete();
            return send_response(true, 'Deleted successfully.');
        } catch (\Throwable $e) {
            return send_response(false, $e->getMessage(), '', 422);
        }
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
