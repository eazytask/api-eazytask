<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\RoasterStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RoasterStatusController extends Controller
{
    public function index()
    {
        $data = RoasterStatus::where('company_code', Auth::user()->company_roles->first()->company->id)->get();
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
            $single = new RoasterStatus;
            $single->name = $request->name;
            $single->remarks = $request->remarks;
            $single->color = $request->color;
            $single->user_id = Auth::id();
            $single->optional = 1;
            $single->company_code = Auth::user()->company_roles->first()->company->id;

            $single->save();

            return send_response(true, 'roster-status added successfully', $single);
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
            $single = RoasterStatus::find($request->id);
            if ($single) {
                if ($request->name)
                    $single->name = $request->name;
                $single->color = $request->color;
                $single->remarks = $request->remarks;
                $single->user_id = Auth::id();
                $single->company_code = Auth::user()->company_roles->first()->company->id;

                $single->update();
            }
            return send_response(true, 'roster-status updated successfully', $single);
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
    }

    public function destroy($id)
    {
        try {
            $single = RoasterStatus::find($id);
            if ($single) {
                $single->delete();
            }
            return send_response(true, 'roster-status deleted successfully');
        } catch (\Throwable $e) {
            return send_response(false, 'sorry! this roster-status used somewhere');
        }
    }
}
