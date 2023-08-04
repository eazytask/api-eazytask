<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\admin\TimekeeperResource;
use App\Models\Employee;
use App\Models\Project;
use App\Models\TimeKeeper;
use App\Notifications\NewShiftNotification;
use App\Notifications\UpdateShiftNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TimekeeperController extends Controller
{
    public function index(Request $request)
    {
        $fromRoaster = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
        $toRoaster = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now()->endOfMonth();
        $filter_project = $request->project_id ? ['project_id', $request->project_id] : ['employee_id', '>', 0];

        $timekeepers = TimeKeeper::where([
            ['company_code', Auth::user()->company_roles->first()->company->id],
            ['roaster_type', 'Unschedueled'],
            $filter_project
        ])
            ->whereBetween('roaster_date', [$fromRoaster, $toRoaster])
            ->orderBy('roaster_date', 'desc')->get();

        return send_response(true, '', TimekeeperResource::collection($timekeepers));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required',
            'job_type_id' => 'required',
            'employee_id' => 'required',
            'roaster_date' => 'required',
            'shift_start' => 'required',
            'shift_end' => 'required',
            'duration' => 'required',
            'ratePerHour' => 'required',
            'amount' => 'required',
        ]);

        if ($validator->fails())
            return send_response(false, 'validation error!', $validator->errors(), 400);

        try {
            $project = Project::find($request->project_id);
            if ($project) {
                $shift_start = Carbon::parse($request->roaster_date . $request->shift_start);
                // $shift_end = Carbon::parse($request->roaster_date . $request->shift_end);
                $shift_end = Carbon::parse($shift_start)->addMinute($request->duration * 60);

                $total_hour = $shift_start->floatDiffInRealHours($shift_end);
                $duration = round($total_hour, 2);
                $rate = $request->ratePerHour;
                $errors = [];
                // if(round($duration, 2) != round($request->duration, 2)){
                //     $errors['duration'] = 'duration is not correct!';
                // }
                if(!is_active_employee($request->employee_id)){
                    $errors['employee_id'] = "this employee's license is expired!";
                }

                if ($duration * $rate != $request->amount) {
                    $errors['amount'] = 'amount is not correct!';
                }
                if (count($errors)) {
                    return send_response(false, 'validation error!', $errors, 400);
                }
                
                $timekeeper = new TimeKeeper();
                if ($request->roaster_type) {
                    if ($request->roaster_type == 'Schedueled') {
                        $timekeeper->roaster_type = 'Schedueled';
                    } elseif ($request->roaster_type == 'Unschedueled' && Carbon::parse($request->roaster_date) > Carbon::now()) {
                        return send_response(false, 'validation error!', ['roaster_date' => "advance date not support for unschedule"], 400);
                    } else {
                        $timekeeper->roaster_type = 'Unschedueled';
                    }
                } else {
                    $timekeeper->roaster_type = 'Unschedueled';
                }

                $timekeeper->user_id = Auth::id();
                $timekeeper->employee_id = $request->employee_id;
                $timekeeper->project_id = $request->project_id;
                $timekeeper->client_id = $project->clientName;
                $timekeeper->employee_id = $request->employee_id;
                $timekeeper->company_id = Auth::id();
                $timekeeper->roaster_date = Carbon::parse($request->roaster_date);
                $timekeeper->shift_start = $shift_start;
                $timekeeper->shift_end = $shift_end;
                $timekeeper->company_code = Auth::user()->company_roles->first()->company->id;
                $timekeeper->duration = $duration;
                $timekeeper->ratePerHour = $rate;
                $timekeeper->amount = $duration * $rate;
                $timekeeper->job_type_id = $request->job_type_id;
                // if ($request->roaster_status_id) {
                //     $timekeeper->roaster_status_id = $request->roaster_status_id;
                // } else {
                $timekeeper->roaster_status_id = roaster_status('Published');
                // }

                if ($request->is_approved) {
                    $timekeeper->is_approved = 1;
                }
                $timekeeper->remarks = $request->remarks;
                $timekeeper->save();

                if ($request->roaster_type == 'Schedueled') {
                    $pro = $timekeeper->project;
                    if ($timekeeper->roaster_status_id == roaster_status("Accepted")) {
                        $noty = 'one of your shift ' . $pro->pName . ' week ending ' . Carbon::parse($timekeeper->roaster_date)->endOfWeek()->format('d-m-Y') . ' has been updated.';
                        push_notify('Shift Update:', $noty . ' Please check eazytask for changes.', $timekeeper->employee->employee_role, $timekeeper->employee->firebase, 'upcoming-shift');
                        $timekeeper->employee->user->notify(new UpdateShiftNotification($noty,$timekeeper));
                    } elseif ($timekeeper->roaster_status_id == roaster_status("Published")) {
                        $noty = 'There is an shift at ' . $pro->pName . ' for week ending ' . Carbon::parse($timekeeper->roaster_date)->endOfWeek()->format('d-m-Y');
                        push_notify('Shift Alert :', $noty . ' Please log on to eazytask to accept / declined it.', $timekeeper->employee->employee_role, $timekeeper->employee->firebase, 'unconfirmed-shift');
                        $timekeeper->employee->user->notify(new UpdateShiftNotification($noty,$timekeeper));
                    }
                }
                return send_response(true, 'roster added successfully', new TimekeeperResource($timekeeper));
            } else {
                return send_response(false, 'invalid project id', [], 400);
            }
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'timekeeper_id' => 'required',
            'project_id' => 'required',
            'job_type_id' => 'required',
            'employee_id' => 'required',
            'roaster_date' => 'required',
            'shift_start' => 'required',
            'shift_end' => 'required',
            'duration' => 'required',
            'ratePerHour' => 'required',
            'amount' => 'required',
        ]);

        if ($validator->fails())
            return send_response(false, 'validation error!', $validator->errors(), 400);

        try {
            if(!is_active_employee($request->employee_id)){
                $errors['employee_id'] = "this employee's license is expired!";
            }
            if (count($errors)) {
                return send_response(false, 'validation error!', $errors, 400);
            }
            return $this->updateModule($request);
        } catch (\Throwable $e) {
            return send_response(false, $e->getMessage(), [], 400);
        }
    }

    public function delete($id)
    {
        try {
            $timekeeper = TimeKeeper::find($id);
            if ($timekeeper) {
                $timekeeper->delete();
            }
            return send_response(true, 'roster deleted successfully');
        } catch (\Throwable $e) {
            return send_response(false, 'something went wrong!', 400);
        }
    }

    public function updateModule($request)
    {
        $project = Project::find($request->project_id);
        if ($project) {
            $timekeeper = TimeKeeper::find($request->timekeeper_id);

            if ($request->roaster_type) {
                if ($request->roaster_type == 'Schedueled') {
                    $timekeeper->roaster_type = 'Schedueled';
                } elseif ($request->roaster_type == 'Unschedueled' && Carbon::parse($request->roaster_date) > Carbon::now()) {
                    return send_response(false, 'validation error!', ['roaster_date' => "advance date not support for unschedule"], 400);
                } else {
                    $timekeeper->roaster_type = 'Unschedueled';
                }
            } else {
                $timekeeper->roaster_type = 'Unschedueled';
            }

            $request->roaster_date =  Carbon::parse($request->roaster_date)->format('d-m-Y');
            $shift_start = Carbon::parse($request->roaster_date . $request->shift_start);
            $shift_end = Carbon::parse($shift_start)->addMinute($request->duration * 60);

            $total_hour = $shift_start->floatDiffInRealHours($shift_end);
            $duration = round($total_hour, 2);
            $rate = $request->ratePerHour;
            $errors = [];
            if ($duration * $rate != $request->amount) {
                $errors['amount'] = 'amount is not correct!';
            }
            if (count($errors)) {
                return send_response(false, 'validation error!', $errors, 400);
            }

            if ($timekeeper) {
                $timekeeper->employee_id = $request->employee_id;
                $timekeeper->client_id = $project->clientName;
                $timekeeper->project_id = $request->project_id;
                $timekeeper->roaster_date = Carbon::parse($request->roaster_date);
                $timekeeper->shift_start = $shift_start;
                $timekeeper->shift_end = $shift_end;
                $timekeeper->duration = $duration;
                $timekeeper->ratePerHour = $rate;
                $timekeeper->amount = $duration * $rate;
                $timekeeper->job_type_id = $request->job_type_id;
                if ($request->roaster_status_id) {
                    $timekeeper->roaster_status_id = $request->roaster_status_id;
                } else {
                    $timekeeper->roaster_status_id = roaster_status('Published');
                }

                if ($request->is_approved) {
                    $timekeeper->is_approved = 1;
                }
                if ($request->sing_in) {
                    $timekeeper->sing_in = $request->sing_in;
                }
                if ($request->sing_out) {
                    $timekeeper->sing_out = $request->sing_out;
                }
                $timekeeper->remarks = $request->remarks;
                $timekeeper->updated_at = Carbon::now();
                $timekeeper->save();

                if ($request->roaster_type == 'Schedueled') {
                    $pro = $timekeeper->project;
                    if ($timekeeper->roaster_status_id == roaster_status("Accepted")) {
                        $noty = 'one of your shift ' . $pro->pName . ' week ending ' . Carbon::parse($timekeeper->roaster_date)->endOfWeek()->format('d-m-Y') . ' has been updated.';
                        push_notify('Shift Update:', $noty . ' Please check eazytask for changes.', $timekeeper->employee->employee_role, $timekeeper->employee->firebase, 'upcoming-shift');
                        $timekeeper->employee->user->notify(new UpdateShiftNotification($noty,$timekeeper));
                    } elseif ($timekeeper->roaster_status_id == roaster_status("Published")) {
                        $noty = 'There is an shift at ' . $pro->pName . ' for week ending ' . Carbon::parse($timekeeper->roaster_date)->endOfWeek()->format('d-m-Y');
                        push_notify('Shift Alert :', $noty . ' Please log on to eazytask to accept / declined it.', $timekeeper->employee->employee_role, $timekeeper->employee->firebase, 'unconfirmed-shift');
                        $timekeeper->employee->user->notify(new UpdateShiftNotification($noty,$timekeeper));
                    }
                }
                return send_response(true, 'roster updated successfully', new TimekeeperResource($timekeeper));
            } else {
                return send_response(false, 'invalid roster id', [], 400);
            }
        } else {
            return send_response(false, 'invalid project id', [], 400);
        }
    }
}
