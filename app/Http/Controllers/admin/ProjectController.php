<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\admin\ProjectResource;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::where('company_code', Auth::user()->company_roles->first()->company->id)->orderBy('pName', 'asc')->get();
        return send_response(true, '', ProjectResource::collection($projects));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pName' => 'required',
            'cName' => 'required',
            'cNumber' => 'required',
            'clientName' => 'required',
            'Status' => 'required',
            'project_address' => 'required',
            'suburb' => 'required',
            'project_state' => 'required',
            'postal_code' => 'required',
            // 'lat' => 'required',
            // 'lon' => 'required',
        ]);
        if ($validator->fails())
            return send_response(false, 'validation error!', $validator->errors(),400);

        try {
            $project = new Project();
            $project->user_id = Auth::id();
            $project->pName = $request->pName;
            $project->cName = $request->cName;
            $project->Status = $request->Status;
            $project->cNumber = $request->cNumber;
            $project->suburb = $request->suburb;
            $project->project_address = $request->project_address;
            // $project->project_venue = $request->project_venue;
            $project->postal_code = $request->postal_code;
            $project->project_state = $request->project_state;
            if(isset($request->lat)){
                $project->lat = $request->lat;
            }
            if(isset($request->lon)){
                $project->lon = $request->lon;
            }
            
            $project->company_code = Auth::user()->company_roles->first()->company->id;
            $project->clientName = $request->clientName;

            $project->save();
            return send_response(true, 'project added successfully', new ProjectResource($project));
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'pName' => 'required',
            'cName' => 'required',
            'cNumber' => 'required',
            'clientName' => 'required',
            'Status' => 'required',
            'project_address' => 'required',
            'suburb' => 'required',
            'project_state' => 'required',
            'postal_code' => 'required',
            'lat' => 'required',
            'lon' => 'required',
        ]);
        if ($validator->fails())
            return send_response(false, 'validation error!', $validator->errors(),400);

        try {
            $project = Project::find($request->id);

            $project->user_id = Auth::id();
            $project->pName = $request->pName;
            $project->cName = $request->cName;
            $project->Status = $request->Status;
            $project->cNumber = $request->cNumber;
            $project->suburb = $request->suburb;
            $project->project_address = $request->project_address;
            // $project->project_venue = $request->project_venue;
            $project->postal_code = $request->postal_code;
            $project->project_state = $request->project_state;
            $project->clientName = $request->clientName;
            $project->lat = $request->lat;
            $project->lon = $request->lon;

            $project->save();
            return send_response(true, 'project updated successfully', new ProjectResource($project));
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
    }

    public function delete($id)
    {
        try {
            $project = Project::find($id);
            $project->delete();
            
            return send_response(true, 'project deleted successfully');
        } catch (\Throwable $e) {
            return send_response(false, 'sorry! this project used somewhere',400);
        }
    }
}
