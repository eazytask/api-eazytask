<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\admin\UnavailabilityResource;
use App\Models\Myavailability;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use DB;

class UnavailabilityController extends Controller
{
    // THIS IS WHAT WE USED 
    // NOT LEAVE CONTROLLER
    public function index(Request $request)
    {
        $user = Auth::user();

        $employee = null;
        $current_role = $user->company_roles->sortByDesc('last_login')->first()->role;
        $current_company = $user->company_roles->first()->company->id;

        if ($current_role > 2) {
            $employee = DB::table('employees')->where('userID', Auth::user()->id)->where('company', $current_company)->first();
        }

        $data = Myavailability::where([
            // ['company_code', Auth::user()->company_roles->first()->company->id],
            ['end_date','>=',Carbon::now()],
            // ['is_leave', 0]
        ])
        ->when($employee != null, function($q) use ($employee) {
            return $q->where('employee_id', $employee->id);
        })
        ->leftJoin('employees', 'employees.id', '=', 'myavailabilities.employee_id')
        ->select('myavailabilities.*', 'employees.fname', 'employees.mname', 'employees.lname', 'employees.image')
        ->orderBy('myavailabilities.employee_id', 'asc')
        ->get();

        // return send_response(true, '', UnavailabilityResource::collection($data));
        return send_response(true, '', $data);
    }

    public function index_total(Request $request) {
        $total_employee = DB::table('myavailabilities')
            ->select(DB::raw(
                'e.id,e.fname,e.mname,e.lname,
        sum(myavailabilities.total) as total_day'
            ))
            ->leftJoin('employees as e', 'e.id', 'myavailabilities.employee_id')
            ->where([
                ['myavailabilities.status', 'approved'],
                ['myavailabilities.company_code', Auth::user()->company_roles->first()->company->id],
                // ['myavailabilities.start_date', '>=', $start_date],
                // // ['myavailabilities.end_date', '<=', $end_date],
                // ['myavailabilities.start_date', '<=', $end_date],
                ['myavailabilities.is_leave', 0]
            ])
            ->groupBy("e.id")
            ->orderBy('fname','asc')
            ->get();

        foreach ($total_employee as $key => $item) {
            $list = Myavailability::where([
                    ['employee_id', $item->id],
                    ['company_code', Auth::user()->company_roles->first()->company->id],
                    // ['start_date', '>=', Carbon::parse($start_date)->toDateString()],
                    // // ['end_date', '<=', Carbon::parse($end_date)->toDateString()],
                    // ['start_date', '<=', Carbon::parse($end_date)->toDateString()],
                    ['status', '>=', 'approved'],
                ])
                ->join('leave_types', 'leave_types.id', '=', 'myavailabilities.leave_type_id')
                ->select('myavailabilities.*', 'leave_types.name as leave_name')
                ->orderBy('start_date', 'desc')
                ->get();

            $item->availabilities = $list;
        }

        return send_response(true, '', $total_employee);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // 'employee_id' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'leave_type_id' => 'required',
        ]);
        if ($validator->fails())
            return send_response(false, 'validation error!', $validator->errors(), 400);

        $user = Auth::user();

        $employee = $request->employee_id ?? null;
        $status = $request->status ?? null;
        $current_role = $user->company_roles->sortByDesc('last_login')->first()->role;
        $current_company = $user->company_roles->first()->company->id;

        if ($current_role > 2) {
            $employee = DB::table('employees')->where('userID', Auth::user()->id)->where('company', $current_company)->first()->id;
            $status = 'pending';
        }
    
        try {
            $single = new Myavailability();
            $single->user_id = Auth::id();
            $single->employee_id = $employee;
            $single->company_code = Auth::user()->company_roles->first()->company->id;
            $single->remarks = $request->remarks;
            $single->start_date = Carbon::parse($request->start_date);
            $single->end_date = Carbon::parse($request->end_date);
            $single->leave_type_id = $request->leave_type_id;
            $single->total = $single->start_date->floatDiffInRealDays($single->end_date) + 1;
            $single->status = $status;
            $single->save();

            return send_response(true, 'availability added successfully', new UnavailabilityResource($single));
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            // 'employee_id' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'leave_type_id' => 'required',
        ]);
        if ($validator->fails())
            return send_response(false, 'validation error!', $validator->errors(), 400);

        try {
            $single = Myavailability::find($request->id);
            if ($single) {
                $single->employee_id = $request->employee_id ?? $single->employee_id;
                $single->remarks = $request->remarks;
                $single->start_date = Carbon::parse($request->start_date);
                $single->end_date = Carbon::parse($request->end_date);
                $single->leave_type_id = $request->leave_type_id;
                $single->total = $single->start_date->floatDiffInRealDays($single->end_date) + 1;
                $single->status = $request->status ?? $single->status;

                $single->save();
            }
            return send_response(true, 'availability updated successfully', new UnavailabilityResource($single));
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
    }

    public function destroy($id)
    {
        try {
            $single = Myavailability::find($id);
            if ($single) {
                $single->delete();
            }
            return send_response(true, 'unavailability deleted successfully');
        } catch (\Throwable $e) {
            return send_response(false, 'something went wrong!');
        }
    }
}
