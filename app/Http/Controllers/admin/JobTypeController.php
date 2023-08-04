<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\JobType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class JobTypeController extends Controller
{
    public function index()
    {
        $data = JobType::where('company_code', Auth::user()->company_roles->first()->company->id)->get();
        return send_response(true, '', $data);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);
        if ($validator->fails())
            return send_response(false, 'validation error!', $validator->errors(), 400);

        try {
            $single = new JobType;
            $single->name = $request->name;
            $single->remarks = $request->remarks;
            $single->user_id = Auth::id();
            $single->company_code = Auth::user()->company_roles->first()->company->id;
            $single->save();

            return send_response(true, 'job-type added successfully', $single);
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
    }


    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'name' => 'required',
        ]);
        if ($validator->fails())
            return send_response(false, 'validation error!', $validator->errors(), 400);

        try {
            $single = JobType::find($request->id);
            if ($single) {

                $single->name = $request->name;
                $single->remarks = $request->remarks;
                $single->user_id = Auth::id();
                $single->company_code = Auth::user()->company_roles->first()->company->id;

                $single->save();
            }
            return send_response(true, 'job-type updated successfully', $single);
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
    }

    public function destroy($id)
    {
        try {
            $single = JobType::find($id);
            if ($single) {
                $single->delete();
            }
            return send_response(true, 'job-type deleted successfully');
        } catch (\Throwable $e) {
            return send_response(false, 'sorry! this job-type used somewhere');
        }
    }
}
