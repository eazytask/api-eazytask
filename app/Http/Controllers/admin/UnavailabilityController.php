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
        if ($user->current_role > 2) {
            $employee = DB::table('employees')->where('userID', Auth::user()->id)->where('company', $user->company_roles->first()->company->id)->first();
        }

        $data = Myavailability::where([
            ['company_code', Auth::user()->company_roles->first()->company->id],
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

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'leave_type_id' => 'required',
        ]);
        if ($validator->fails())
            return send_response(false, 'validation error!', $validator->errors(), 400);

        try {
            $single = new Myavailability();
            $single->user_id = Auth::id();;
            $single->employee_id = $request->employee_id;
            $single->company_code = Auth::user()->company_roles->first()->company->id;
            $single->remarks = $request->remarks;
            $single->start_date = Carbon::parse($request->start_date);
            $single->end_date = Carbon::parse($request->end_date);
            $single->leave_type_id = $request->leave_type_id;
            $single->total = $single->start_date->floatDiffInRealDays($single->end_date) + 1;
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
            'employee_id' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'leave_type_id' => 'required',
        ]);
        if ($validator->fails())
            return send_response(false, 'validation error!', $validator->errors(), 400);

        try {
            $single = Myavailability::find($request->id);
            if ($single) {
                $single->employee_id = $request->employee_id;
                $single->remarks = $request->remarks;
                $single->start_date = Carbon::parse($request->start_date);
                $single->end_date = Carbon::parse($request->end_date);
                $single->leave_type_id = $request->leave_type_id;
                $single->total = $single->start_date->floatDiffInRealDays($single->end_date) + 1;
                $single->status = $request->status;

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
