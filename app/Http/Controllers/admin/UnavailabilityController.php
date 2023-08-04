<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\admin\UnavailabilityResource;
use App\Models\Myavailability;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UnavailabilityController extends Controller
{
    public function index()
    {
        $data = Myavailability::where([
            ['company_code', Auth::user()->company_roles->first()->company->id],
            ['end_date','>=',Carbon::now()],
            ['is_leave', 0]
        ])->orderBy('employee_id', 'asc')->get();

        return send_response(true, '', UnavailabilityResource::collection($data));
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
