<?php

namespace App\Http\Controllers;

use App\Http\Resources\user\CompanyResource;
use App\Http\Resources\user\UserRoleResource;
use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class SwitchCompanyController extends Controller
{
    public function index()
    {
        $companies = [];
        foreach (Auth::user()->user_roles->unique('company_code') as $company) {
            if ($company->company->status == 1 && Carbon::parse($company->company->expire_date)>Carbon::now()->toDateString()) {
                array_push($companies, $company->company);
            }
        }
        return send_response(true, '', CompanyResource::collection($companies));
    }

    public function admin_companies()
    {
        $companies = [];
        foreach (Auth::user()->user_roles->where('role',2)->unique('company_code') as $company) {
            if ($company->company->status == 1 && Carbon::parse($company->company->expire_date)>Carbon::now()->toDateString()) {
                array_push($companies, $company->company);
            }
        }
        return send_response(true, '', CompanyResource::collection($companies));
    }

    public function current_company()
    {
        $data  = [
            'id' => Auth::user()->company_roles->first()->company->id,
            'company_code' => Auth::user()->company_roles->first()->company->company_code,
            'company_name' => Auth::user()->company_roles->first()->company->company,
            'roles' => UserRoleResource::collection(Auth::user()->company_roles->where('status',1))
        ];
        return send_response(true, '', $data);
    }

    public function switch($company_id)
    {
        try {
            $company = Company::find($company_id);dd($company);
            if ($company) {
                $all_roles = auth()->user()->user_roles;
                $define_last = true;
                foreach ($all_roles as $role) {
                    if($role->company_code  == $company->id && $define_last){
                        $last_login=1;
                        $define_last = false;
                    }else{
                        $last_login=0;
                    }
                    $role->last_login = $last_login;
                    $role->save();
                }
                return send_response(true, 'company successfully switched.');
            }
            return send_response(false, 'something went wrong!',400);
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
    }

    public function switch_role($switch_role)
    {
        try {
            $all_roles = auth()->user()->user_roles;
            foreach ($all_roles as $role) {
                $role->last_login = $role->id  == $switch_role ? 1 : 0;
                $role->save();
            }
            return send_response(true, 'role successfully changed.');
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
    }
}
