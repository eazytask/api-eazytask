<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\admin\RevenueResource;
use App\Models\Project;
use App\Models\Revenue;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RevenueController extends Controller
{
    public function index()
    {
        $revenues = Revenue::where([
            ['user_id', Auth::id()],
            ['payment_date','>=',Carbon::now()->subMonths(3)->toDateString()]
        ])->get();
        return send_response(true, '', RevenueResource::collection($revenues));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_name' => 'required',
            'roaster_date_from' => 'required',
            'roaster_date_to' => 'required',
            'hours' => 'required',
            'rate' => 'required',
            'amount' => 'required',
            'payment_date' => 'required',
        ]);

        if ($validator->fails())
            return send_response(false, 'validation error!', $validator->errors(), 400);

        try {
            $project = Project::find($request->project_name);
            $revenues = new Revenue();
            $revenues->user_id = Auth::id();
            $revenues->company_code = Auth::user()->company_roles->first()->company->id;
            $revenues->client_name = $project->clientName;
            $revenues->project_name = $request->project_name;
            $revenues->payment_date = Carbon::parse($request->payment_date);
            $revenues->roaster_date_from = Carbon::parse($request->roaster_date_from);
            $revenues->roaster_date_to = Carbon::parse($request->roaster_date_to);
            $revenues->rate = $request->rate;
            $revenues->hours = $request->hours;
            $revenues->amount = $request->amount;
            $revenues->remarks = $request->remarks;
            $revenues->created_at = Carbon::now();
            $revenues->save();
            return send_response(true, 'revenue added successfully', new RevenueResource($revenues));
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'project_name' => 'required',
            'roaster_date_from' => 'required',
            'roaster_date_to' => 'required',
            'hours' => 'required',
            'rate' => 'required',
            'amount' => 'required',
            'payment_date' => 'required',
        ]);

        if ($validator->fails())
            return send_response(false, 'validation error!', $validator->errors(), 400);

        try {
            $project = Project::find($request->project_name);

            $revenues = Revenue::find($request->id);
            $revenues->client_name = $project->clientName;
            $revenues->project_name = $request->project_name;
            $revenues->payment_date = Carbon::parse($request->payment_date);
            $revenues->roaster_date_from = Carbon::parse($request->roaster_date_from);
            $revenues->roaster_date_to = Carbon::parse($request->roaster_date_to);
            $revenues->rate = $request->rate;
            $revenues->hours = $request->hours;
            $revenues->amount = $request->amount;
            $revenues->remarks = $request->remarks;
            $revenues->updated_at = Carbon::now();
            $revenues->save();
            return send_response(true, 'revenue updated successfully', new RevenueResource($revenues));
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
    }

    public function delete($id)
    {
        try {
            $revenues = Revenue::find($id);
            if ($revenues) {
                $revenues->delete();
            }
            return send_response(true, 'revenue deleted successfully');
        } catch (\Throwable $e) {
            return send_response(false, 'something went wrong!');
        }
    }

    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required',
            'end_date' => 'required',
        ]);

        if ($validator->fails())
            return send_response(false, 'validation error!', $validator->errors(), 400);

        try {
            $filter_roaster_from = $request->start_date ? ['roaster_date_from', '>=', Carbon::parse($request->start_date)] : ['id', '>', 0];
            $filter_roaster_to = $request->end_date ? ['roaster_date_to', '<=', Carbon::parse($request->end_date)] : ['id', '>', 0];
            $filter_project = $request->project_id ? ['project_name', $request->project_id] : ['id', '>', 0];
            $filter_client = $request->client_id ? ['client_name', $request->client_id] : ['id', '>', 0];
            $revenues = Revenue::where('user_id', Auth::id())
                ->where([
                    ['company_code', Auth::user()->company_roles->first()->company->id],
                    $filter_roaster_from,
                    $filter_roaster_to,
                    $filter_project,
                    $filter_client
                ])
                ->get();
            return send_response(true, '', RevenueResource::collection($revenues));
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
    }
}
