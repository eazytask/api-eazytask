<?php

namespace App\Http\Controllers;

use App\Models\Compliance;
use App\Models\Employee;
use App\Models\JobType;
use App\Models\LeaveType;
use App\Models\Project;
use App\Models\RoasterStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BasicController extends Controller
{
    public function job_type(){
        $job_types = JobType::where([
            ['company_code',auth()->user()->company_roles->first()->company->id]
        ])->get();
        
        return send_response(true, '', $job_types);
    }

    public function roster_status(){
        $roster_status = RoasterStatus::where([
            ['company_code',auth()->user()->company_roles->first()->company->id]
        ])->get();
        
        return send_response(true, '', $roster_status);
    }
    
    public function projects($status=null){
        if($status=='all'){
            $filter_status = ['company_code','>',0];
        }else{
            $filter_status =  ['status',1];
        }
            
        $projects = Project::where([
            ['company_code',auth()->user()->company_roles->first()->company->id],
            $filter_status
        ])->get();
        
        return send_response(true, '', $projects);
    }

    public function employees($status=null)
    {
        if($status=='all'){
            $filter_status = ['company','>',0];
        }else{
            $filter_status =  ['status',1];
        }
          
        $employees = Employee::where([
            ['company', Auth::user()->company_roles->first()->company->id],
            ['role',3],
            $filter_status
        ])
        ->where(function ($q) {
          avoid_expired_license($q);
        })
        ->orderBy('fname', 'asc')->get();
        return send_response(true, '', $employees);
    }

    public function compliances(){
        $comp = Compliance::all();
        return send_response(true, '', $comp);
    }

    public function leave_type(){
        $leave_types = LeaveType::all();
        return send_response(true, '', $leave_types);
    }
}
