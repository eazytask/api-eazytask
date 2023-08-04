<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use App\Http\Resources\user\EventResource;
use App\Models\Eventrequest;
use App\Models\Upcomingevent;
use App\Notifications\EventRequestNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UpcomingEventController extends Controller
{
    public function index()
    {
        $upcomingevents = Upcomingevent::where([
            ['company_code', Auth::user()->employee->company],
            ['event_date', '>', Carbon::now()]
        ])
            ->orderBy('event_date', 'asc')
            ->with('project', 'already_applied')
            ->get();
        // ->paginate(1);
        return send_response(true, '', EventResource::collection($upcomingevents));
    }

    public function event_confirm(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required'
        ]);
        if ($validator->fails())
            return send_response(false, 'validation error!', $validator->errors(), 400);
        try {
            $eventrequests = new Eventrequest();
            $eventrequests->event_id = $request->event_id;
            $eventrequests->employee_id = Auth::user()->employee->id;
            $eventrequests->user_id = Auth::id();
            $eventrequests->company_code = Auth::user()->employee->company;
            $eventrequests->created_at = Carbon::now();
            $eventrequests->save();

            $event = $eventrequests->upcomingevent;
            $pro = $event->project;
            $msg = Auth::user()->name . ' is interested in "' . $pro->pName . '" event on ' . Carbon::parse($event->event_date)->format('d-m-Y') . '(' . Carbon::parse($event->shift_start)->format('H:i') . '-' . Carbon::parse($event->shift_end)->format('H:i') . ') near "' . $pro->project_address . ' ' . $pro->suburb . ' ' . $pro->project_state . '"';

            Auth::user()->employee->admin->notify(new EventRequestNotification($msg));
            push_notify('New Event Request :', $msg,Auth::user()->employee->employee_role, Auth::user()->employee->admin->firebase,'admin-event',$event->id);
            return send_response(true, 'thanks for your interest.', []);
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
    }
}
