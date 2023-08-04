<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\admin\InductedResource;
use App\Models\Inductedsite;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class InductedController extends Controller
{
    public function index()
    {
        $inductions = Inductedsite::where([
            ['company_code', Auth::user()->company_roles->first()->company->id],
        ])->orderBy('employee_id','asc')->get();
        return send_response(true, '', InductedResource::collection($inductions));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required',
            'employee_id' => 'required',
            'induction_date' => 'required',
        ]);

        if ($validator->fails())
            return send_response(false, 'validation error!', $validator->errors(), 400);

        try {
            $already_has = Inductedsite::where([
                ['employee_id', $request->employee_id],
                ['project_id', $request->project_id],
                ['company_code', Auth::user()->company_roles->first()->company->id]
            ])->first();
    
            if ($already_has) {
                $inductedsites = $already_has;
            } else {
                $project = Project::find($request->project_id);
    
                $inductedsites = new Inductedsite();
                $inductedsites->user_id = Auth::id();
                $inductedsites->company_code = Auth::user()->company_roles->first()->company->id;
                $inductedsites->employee_id = $request->employee_id;
                $inductedsites->client_id = $project->clientName;
                $inductedsites->project_id = $request->project_id;
            }
            $inductedsites->induction_date = Carbon::parse($request->induction_date);
            $inductedsites->remarks = $request->remarks;
            $inductedsites->save();

            return send_response(true, 'inducted added successfully', new InductedResource($inductedsites));
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'project_id' => 'required',
            'employee_id' => 'required',
            'induction_date' => 'required',
        ]);

        if ($validator->fails())
            return send_response(false, 'validation error!', $validator->errors(), 400);

        try {
            $already_has = Inductedsite::where([
                ['employee_id', $request->employee_id],
                ['project_id', $request->project_id],
                ['company_code', Auth::user()->company_roles->first()->company->id]
            ])->first();
    
            if ($already_has) {
                if ($already_has->id == $request->id) {
                    $inductedsites = $already_has;
                } else {
                    return send_response(false, 'this inductiction already exist!',[],400);
                }
            } else {
                $project = Project::find($request->project_id);
    
                $inductedsites = Inductedsite::find($request->id);
                $inductedsites->employee_id = $request->employee_id;
                $inductedsites->client_id = $project->clientName;
                $inductedsites->project_id = $request->project_id;
            }
    
            $inductedsites->induction_date = Carbon::parse($request->induction_date);
            $inductedsites->remarks = $request->remarks;
            $inductedsites->save();

            return send_response(true, 'inducted updated successfully', new InductedResource($inductedsites));
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
    }

    public function delete($id)
    {
        try {
            $inductedsite = Inductedsite::find($id);
            $inductedsite->delete();
            return send_response(true, 'inducted deleted successfully');
        } catch (\Throwable $e) {
            return send_response(false, 'something went wrong!',400);
        }
    }
}
