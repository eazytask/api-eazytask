<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use App\Http\Resources\user\UserTimekeeperResource;
use App\Models\TimeKeeper;
use App\Models\User;
use App\Notifications\ConfirmShiftNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserShiftController extends Controller
{
    public function unconfirmed_shift()
    {
        $roasters = TimeKeeper::where([
            ['employee_id', Auth::user()->employee->id],
            ['company_code', Auth::user()->employee->company],
            ['roaster_status_id', roaster_status('Published')],
            ['shift_end', '>=', Carbon::now()],
            // ['roaster_type', 'Schedueled'],
        ])
            ->orderBy('roaster_date', 'asc')
            ->get();
        return send_response(true, '', UserTimekeeperResource::collection($roasters));
    }

    public function confirm_shift(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required',
            'ids' => 'required|array'
        ]);
        if ($validator->fails())
            return send_response(false, 'validation error!', $validator->errors(), 400);
        try {
            $status = $request->action == 'reject' ? roaster_status('Rejected') : roaster_status('Accepted');
            $all_id = $request->ids;
            foreach ($all_id as $id) {
                $roster = TimeKeeper::find($id);
                $roster->roaster_status_id = $status;
                $roster->save();
            }

            if ($all_id) {
                if($request->action=='reject'){
                    $confirm = 'Rejected';
                    $status = 'danger';
                }else{
                    $confirm = 'Accepted';
                    $status = 'success';
                }
                if(count($all_id)==1){
                    $msg = Auth::user()->name.' '.$confirm.' a shift of week ending '. Carbon::parse($roster->roaster_date)->endOfWeek()->format('d-m-Y');
                }else{
                    $msg = Auth::user()->name.' '.$confirm.' shifts of week ending '. Carbon::parse($roster->roaster_date)->endOfWeek()->format('d-m-Y');
                }
                $admin = User::find($roster->user_id);
                $admin->notify(new ConfirmShiftNotification($msg,$request->action,$status));
                push_notify($request->action.' Shift :',$msg. ' Please check eazytask for changes',$admin->admin_role,$admin->firebase,'admin-scheduele-entry');
            }
            return send_response(true, 'successfully confirmed.');
        } catch (\Throwable $e) {
            return send_response(false, $e->getMessage(), '', 422);
        }
    }

    public function upcoming_shift()
    {
        $data = [];
        $start_date = Carbon::now()->addMonths(2)->endOfMonth()->startOfWeek();
        for ($start_date; $start_date->startOfWeek() != Carbon::now()->subWeek()->startOfWeek(); $start_date->subWeek()) {
            $this_data = [];
            $curr_week = $start_date;
            // print($curr_week->startOfWeek()->format('d-m-y').' - '. $curr_week->endOfWeek()->format('d-m-y'). '</br>');
            $rosters = TimeKeeper::where([
                ['employee_id', Auth::user()->employee->id],
                ['company_code', Auth::user()->employee->company],
                ['roaster_status_id', roaster_status('Accepted')],
                ['roaster_type', 'Schedueled'],
                ['shift_end', '>=', Carbon::now()],
                ['sing_in', null]
            ])
                ->whereBetween('roaster_date', [$curr_week->startOfWeek()->format('Y-m-d'), $curr_week->endOfWeek()->format('Y-m-d')])
                ->orderBy('roaster_date', 'asc')
                ->get();

            $this_data['weekend'] = $curr_week->startOfWeek()->format('D, d M - ') . $curr_week->endOfWeek()->format('D, d M ') . $curr_week->startOfWeek()->format('Y');
            $this_data['total_hours'] = (string)round($rosters->sum('duration'),2);
            $this_data['shifts'] = UserTimekeeperResource::collection($rosters);
            array_push($data, $this_data);
        }

        return send_response(true, '', array_reverse($data));
    }

    public function past_shift()
    {
        $data = [];
        $start_date = Carbon::now()->subMonths(2)->startOfMonth()->startOfWeek();
        for ($start_date; $start_date->startOfWeek() != Carbon::now()->addWeeks()->startOfWeek(); $start_date->addWeek()) {
            $this_data = [];
            $curr_week = $start_date;
            // print($curr_week->startOfWeek()->format('d-m-y').' - '. $curr_week->endOfWeek()->format('d-m-y'). '</br>');
            $rosters = TimeKeeper::where([
                ['employee_id', Auth::user()->employee->id],
                ['company_code', Auth::user()->employee->company],
                ['shift_end', '<=', Carbon::now()],
            ])
                ->where(function ($q) {
                    $q->where('sing_in', '!=', null);
                    $q->where('sing_out', '!=', null);
                    $q->orWhere(function ($q) {
                        $q->where('shift_end', '<=', Carbon::now());
                    });
                })
                // ->where(function ($q) {
                //     avoid_rejected_key($q);
                // })
                ->whereBetween('roaster_date', [$curr_week->startOfWeek()->toDateString(), $curr_week->endOfWeek()->toDateString()])
                ->orderBy('roaster_date', 'desc')
                ->get();

            $this_data['weekend'] = $curr_week->startOfWeek()->format('D, d M - ') . $curr_week->endOfWeek()->format('D, d M ') . $curr_week->startOfWeek()->format('Y');
            $this_data['total_hours'] = (string)round($rosters->sum('duration'),2);
            $this_data['shifts'] = UserTimekeeperResource::collection($rosters);
            array_push($data, $this_data);
        }

        return send_response(true, '', array_reverse($data));
    }
}
