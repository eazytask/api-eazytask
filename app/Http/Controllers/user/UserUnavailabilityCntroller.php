<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use App\Models\Myavailability;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserUnavailabilityCntroller extends Controller
{
    public function index()
    {
        $data = Myavailability::where([
            ['user_id', Auth::user()->employee->user_id],
            ['company_code', Auth::user()->employee->company],
            ['is_leave', 0]
        ])
        ->leftJoin('employees', 'employees.id', '=', 'myavailabilities.employee_id')
        ->select('myavailabilities.*', 'employees.fname', 'employees.mname', 'employees.lname')
        ->get();

        return send_response(true, '', $data);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required',
            'end_date' => 'required',
            'leave_type_id' => 'required',
        ]);
        if ($validator->fails())
            return send_response(false, 'validation error!', $validator->errors(), 400);

        try {
            $single = new Myavailability();
            $single->user_id = Auth::user()->employee->user_id;;
            $single->employee_id = Auth::user()->employee->id;
            $single->company_code = Auth::user()->employee->company;
            $single->remarks = $request->remarks;
            $single->start_date = Carbon::parse($request->start_date);
            $single->end_date = Carbon::parse($request->end_date);
            $single->leave_type_id = $request->leave_type_id;
            $single->total = $single->start_date->floatDiffInRealDays($single->end_date) + 1;
            $single->save();

            return send_response(true, 'availability added successfully', $single);
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'leave_type_id' => 'required',
        ]);
        if ($validator->fails())
            return send_response(false, 'validation error!', $validator->errors(), 400);

        try {
            $single = Myavailability::find($request->id);
            if ($single) {
                $single->remarks = $request->remarks;
                $single->start_date = Carbon::parse($request->start_date);
                $single->end_date = Carbon::parse($request->end_date);
                $single->leave_type_id = $request->leave_type_id;
                $single->total = $single->start_date->floatDiffInRealDays($single->end_date) + 1;

                $single->save();
            }
            return send_response(true, 'availability updated successfully', $single);
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
