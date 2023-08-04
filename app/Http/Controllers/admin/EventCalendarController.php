<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\admin\EmployeeResource;
use App\Http\Resources\admin\EventResource;
use App\Models\Employee;
use App\Models\Eventrequest;
use App\Models\Inductedsite;
use App\Models\Project;
use App\Models\TimeKeeper;
use App\Models\Upcomingevent;
use App\Notifications\NewEventNotification;
use App\Notifications\NewShiftNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EventCalendarController extends Controller
{
    public function index(Request $request)
    {
        $filter_project = $request->project_id ? ['project_name', $request->project_id] : ['project_name', '>', 0];
        $month = Carbon::parse($request->month);
        $events = Upcomingevent::where([
            $filter_project,
            ['company_code', Auth::user()->company_roles->first()->company->id]
        ])
            ->whereBetween('event_date', [$month->startOfMonth()->toDateString(), $month->endOfMonth()->toDateString()])
            ->get();

        $data = [];
        $i = 0;

        foreach ($events as $key => $value) {

            $event_requests = Eventrequest::where('event_id', $value->id)->get();
            if (Carbon::parse($value->event_date)->toDateString() < Carbon::now()->toDateString()) {
                $status = $event_requests->count() ? '#7367f0' : '#F8C471';
                // $value['latest'] = false;
            } else {
                $status = $event_requests->count() ? '#82E0AA' : '#F93737';
                // $value['latest'] = true;
            }
            // $value['calendar'] = $status;
            // $value['employees'] = $employees;

            $data[$i]['id'] = $value->id;
            $data[$i]['project_name'] = $value->project->pName;
            $data[$i]['shift_start'] = $value->shift_start;
            $data[$i]['shift_end'] = $value->shift_end;
            $data[$i]['color'] = $status;
            $data[$i]['shift'] = new EventResource($value);

            // $data[$i]['employees'] = $employees;
            // $data[$i]['start'] = Carbon::createFromFormat('Y-m-d H:i:s', Carbon::parse(date('Y-m-d',strtotime($value->shift_start)),'UTC'));          
            // $data[$i]['end'] = Carbon::createFromFormat('Y-m-d H:i:s', Carbon::parse(date('Y-m-d',strtotime($value->shift_end)),'UTC'));

            $data[$i]['description'] = "event desctiption";
            $i++;
        }
        return send_response(true, '', $data);
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'project_id' => 'required',
                'event_date' => 'required',
                'shift_start' => 'required',
                'shift_end' => 'required',
                'job_type_id' => 'required',
                'rate' => 'required',
                'duration' => 'required',
            ]);
            if ($validator->fails())
                return send_response(false, 'validation error!', $validator->errors(), 400);

            if (Carbon::parse($request->event_date) < Carbon::now()->toDateString()) {
                return send_response(false, 'validation error!', ['event_date' => "previous date not supported!"], 400);
            }

            $project = Project::find($request->project_id);
            $shift_start = Carbon::parse($request->event_date . $request->shift_start);
            // return $time_duration = Carbon::parse($request->shift_end)->diffInDays(Carbon::parse($request->shift_start));
            // $shift_end = Carbon::parse($request->event_date . $request->shift_end);
            $shift_end = Carbon::parse($shift_start)->addMinute($request->duration * 60);

            $upcomingevents = new Upcomingevent();
            $upcomingevents->user_id = Auth::id();
            $upcomingevents->company_code = Auth::user()->company_roles->first()->company->id;
            $upcomingevents->client_name = $project->clientName;
            $upcomingevents->project_name = $request->project_id;
            $upcomingevents->job_type_name = $request->job_type_id;
            $upcomingevents->event_date = Carbon::parse($request->event_date);
            $upcomingevents->shift_start = $shift_start;
            $upcomingevents->shift_end = $shift_end;
            $upcomingevents->rate = $request->rate;
            $upcomingevents->remarks = $request->remarks;
            $upcomingevents->save();

            $employees = Employee::where('company', Auth::user()->company_roles->first()->company->id)->get();
            foreach ($employees as $emp) {
                $emp->user->notify(new NewEventNotification('new event published from ' . strtoupper(Auth::user()->company->company_code)));
            }
            return send_response(true, 'event successfully added', new EventResource($upcomingevents));
        } catch (\Throwable $e) {
            return send_response(false, 'something went wrong!', 400);
        }
    }

    public function update(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'event_id' => 'required',
                'project_id' => 'required',
                'event_date' => 'required',
                'shift_start' => 'required',
                'shift_end' => 'required',
                'job_type_id' => 'required',
                'rate' => 'required',
                'duration' => 'required',
            ]);
            if ($validator->fails())
                return send_response(false, 'validation error!', $validator->errors(), 400);

            if (Carbon::parse($request->event_date) < Carbon::now()->toDateString()) {
                return send_response(false, 'validation error!', ['event_date' => "previous date not supported!"], 400);
            }

            $project = Project::find($request->project_id);
            $shift_start = Carbon::parse($request->event_date . $request->shift_start);
            // $shift_end = Carbon::parse($request->event_date . $request->shift_end);
            $shift_end = Carbon::parse($shift_start)->addMinute($request->duration * 60);

            $upcomingevents = Upcomingevent::find($request->event_id);
            $upcomingevents->client_name = $project->clientName;
            $upcomingevents->project_name = $request->project_id;
            $upcomingevents->job_type_name = $request->job_type_id;
            $upcomingevents->event_date = Carbon::parse($request->event_date);
            $upcomingevents->shift_start = $shift_start;
            $upcomingevents->shift_end = $shift_end;
            $upcomingevents->rate = $request->rate;
            $upcomingevents->remarks = $request->remarks;
            $upcomingevents->updated_at = Carbon::now();
            $upcomingevents->save();

            return send_response(true, 'event successfully updated', new EventResource($upcomingevents));
        } catch (\Throwable $e) {
            return send_response(false, 'something went wrong!', 400);
        }
    }

    public function delete($event_id)
    {
        $upcomingevents = Upcomingevent::find($event_id);
        if ($upcomingevents) {
            $upcomingevents->delete();
            return send_response(true, 'event successfully deleted');
        } else {
            return send_response(false, 'invalid event id!', 400);
        }
    }

    public function get_employees($event_id)
    {
        try {
            $event = Upcomingevent::find($event_id);

            $data = [];
            $employees = Employee::where([
                ['company', Auth::user()->company_roles->first()->company->id],
                ['role', 3],
                ['status', 1]
            ])
                ->where(function ($q) {
                    avoid_expired_license($q);
                })
                ->orderBy('fname', 'asc')->get();
            foreach ($employees as $k => $employee) {
                //employee
                $data[$k] = $employee;
                //requested employee
                $requested = Eventrequest::where([
                    ['employee_id', $employee->id],
                    ['event_id', $event->id]
                ])->first();
                $data[$k]['requested'] = $requested ? true : false;

                //inducted employee
                $inducted = Inductedsite::where([
                    ['employee_id', $employee->id],
                    ['user_id', Auth::id()],
                ])->first();
                $data[$k]['inducted'] = $inducted ? true : false;

                $shift_start = $event->shift_start;
                $shift_end = $event->shift_end;

                $employee_status = TimeKeeper::where([
                    ['employee_id', $employee->id],
                    ['user_id', $event->user_id],
                    ['project_id', $event->project_name],
                    ['client_id', $event->client_name],
                    // ['roaster_date', Carbon::parse($value->event_date)],
                ])
                    ->where(function ($q) use ($shift_start, $shift_end) {
                        $q->where('shift_start', '>=', $shift_start);
                        $q->where('shift_start', '<=', $shift_end);
                        $q->orWhere(function ($q) use ($shift_end, $shift_start) {
                            $q->where('shift_end', '>=', $shift_start);
                            $q->where('shift_end', '<=', $shift_end);
                        });
                    })
                    ->count();
                $data[$k]['status'] = $employee_status ? 'Added' : 'Waiting';
            }
            return send_response(true, '', $data);
        } catch (\Throwable $e) {
            return send_response(false, 'something went wrong!', 400);
        }
    }

    public function publish(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'event_id' => 'required',
                'employee_ids' => 'array|required',
            ]);
            if ($validator->fails())
                return send_response(false, 'validation error!', $validator->errors(), 400);

            $event = Upcomingevent::find($request->event_id);
            $pro = $event->project;
            // $msg = 'You assigned a shift for ' . Carbon::parse($event->event_date)->format('d-m-Y') . '
            // (' . Carbon::parse($event->shift_start)->format('H:i') . '-' . Carbon::parse($event->shift_end)->format('H:i') . ')
            // , at "' . $pro->pName . '" ' . $pro->project_address . ' ' . $pro->suburb . ' ' . $pro->project_state . ', ' . $event->job_type->name;

            $msg = 'There is an shift at ' . $pro->pName . ' for week ending ' . Carbon::parse($event->event_date)->endOfWeek()->format('d-m-Y');

            foreach ($request->employee_ids as $employee_id) {

                if (is_active_employee($employee_id)) {

                    $shift_start = Carbon::parse($event->shift_start);
                    $shift_end = Carbon::parse($event->shift_end);

                    $duration = round($shift_start->floatDiffInRealHours($shift_end), 2);

                    $timekeeper = new TimeKeeper();
                    $timekeeper->user_id = Auth::id();
                    $timekeeper->employee_id = $employee_id;
                    $timekeeper->client_id = $event->client_name;
                    $timekeeper->project_id = $event->project_name;
                    $timekeeper->company_id = Auth::id();
                    $timekeeper->roaster_date = Carbon::parse($event->event_date);
                    $timekeeper->shift_start = $shift_start;
                    $timekeeper->shift_end = $shift_end;
                    $timekeeper->company_code = $event->company_code;
                    $timekeeper->ratePerHour = $event->rate;
                    $timekeeper->roaster_status_id = roaster_status('Published');
                    $timekeeper->roaster_type = 'Schedueled';
                    $timekeeper->remarks = $event->remarks;
                    $timekeeper->duration = $duration;
                    $timekeeper->amount = $duration * $event->rate;

                    $timekeeper->job_type_id = $event->job_type_name;
                    // $timekeeper->created_at = Carbon::now();
                    $timekeeper->save();

                    $timekeeper->employee->user->notify(new NewShiftNotification($msg,$timekeeper));
                    push_notify('Shift Alert:', $msg . ' Please log on to eazytask to accept / declined it.', $timekeeper->employee->employee_role, $timekeeper->employee->firebase, 'unconfirmed-shift');
                }
            }
            return send_response(true, 'employees successfully added');
        } catch (\Throwable $e) {
            return send_response(false, 'something went wrong!', 400);
        }
    }
}
