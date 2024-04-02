<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\user\UserRoleResource;
use App\Mail\ForgetPassword;
use App\Models\FirebaseToken;
use App\Models\PasswordReset;
use App\Models\RoasterStatus;
use App\Models\User;
use App\Models\UserRole;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        //validation-----------------------------------------------------
        $input = $request->all();
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);
        if ($validator->fails())
            return send_response(false, 'validation error!', $validator->errors(),400);

        $v_user = User::where('email', $request->email)->first();
        if (!$v_user) {
            return send_response(false, 'validation error!', ['email' => "this email doesn't exist!"],400);
        } elseif (!Hash::check($request->password, $v_user->password)) {
            return send_response(false, 'validation error!', ['password' => "wrong password!"],400);
        }

        //login prosses-------------------------------------------------
        if (auth()->attempt(array('email' => $input['email'], 'password' => $input['password']))) {
            if (auth()->user()->user_roles->count() > 0) {
                if ((auth()->user()->company_roles->first()->role != 1) && (auth()->user()->company_roles->first()->company->status == 0 || Carbon::parse(auth()->user()->company_roles->first()->company->expire_date)<Carbon::now()->toDateString())) {
                    $all_roles = auth()->user()->user_roles->unique('company_code')->sortByDesc('last_login');
                    $company = null;
                    $c_id = '';
                    foreach ($all_roles as $role) {
                        if (!$company && $role->company->status == 1 && Carbon::parse($role->company->expire_date)>Carbon::now()->toDateString()) {
                            $company = $role->company;
                            $c_id = $role->company->id;
                        }
                        $role->last_login = $role->company_code  == $c_id ? 1 : 0;
                        $role->save();
                    }
                    if ($company) {
                        return $this->login_response($v_user, $company->id);
                    } else {
                        Auth::logout();
                        return send_response(false, 'sorry! your company has temporarily blocked!',[],400);
                    }
                } else {
                    $current_company_id = Auth::user()->company_roles->first()->company->id;

                    return $this->login_response($v_user, $current_company_id);
                }
            } else {
                return send_response(false, 'you are not an active user!',[],400);
            }
        } else {
            return send_response(false, 'something went wrong!',[],400);
        }
    }

    private function login_response($v_user, $current_company_id)
    {
        $active_company_roles =  UserRole::where([
            ['user_id', Auth::id()],
            ['company_code', $current_company_id]
        ])->orderBy('last_login', 'desc')->get();

        $roaster_statuses = [];
        if ($active_company_roles->first()->role != 1) {
            $roaster_status = RoasterStatus::where([
                ['company_code', $current_company_id],
            ])->get();

            foreach ($roaster_status as $status) {
                $roaster_statuses[$status->name] = $status->id;
            }
        }

        $user = Auth::user();
        // $companies = [];
        // foreach ($user->user_roles->unique('company_code') as $company) {
        //     array_push($companies, $company->company);
        // }

        $data['user']  = $v_user;
        if($active_company_roles->first()->role == 3){
            $data['user']->mname  = Auth::user()->employee->mname;
            $data['user']->lname  = Auth::user()->employee->lname;
        // }elseif($active_company_roles->first()->role == 4){
        //     $data['user']->mname  = Auth::user()->supervisor->mname;
        //     $data['user']->lname  = Auth::user()->supervisor->lname;
        }else{
            $data['user']->mname  = Auth::user()->company->mname;
            $data['user']->lname  = Auth::user()->company->lname;
        }

        $all_roles=[];
        foreach($active_company_roles as $role){
            array_push($all_roles,$role->role);
        }
        $data['user']['current_company_code']  = $active_company_roles->first()->company->company_code;
        $data['user']['current_company']  = $active_company_roles->first()->company->id;
        $data['user']['current_role']  = $active_company_roles->sortByDesc('last_login')->first()->role;
        $data['user']['roles']  = $all_roles;
        // [
        //     'id'=>$active_company_roles->first()->company->id,
        //     'company_code'=>$active_company_roles->first()->company->company_code,
        //     'company_name'=>$active_company_roles->first()->company->company,
        //     'roles'=>UserRoleResource::collection($active_company_roles)
        // ];
        // $data['all_companies']  = $companies;
        $data['user']['roaster_statuses']  = $roaster_statuses;
        $data['user']['token'] = $user->createToken('AccessToken')->accessToken;
        return send_response(true, 'You are successfully logged in.', $data);
    }

    #route for kiosk admin login
    public function admin_login(Request $request)
    {
        //validation-----------------------------------------------------
        $input = $request->all();
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);
        if ($validator->fails())
            return send_response(false, 'validation error!', $validator->errors(),400);

        $v_user = User::where('email', $request->email)->first();
        if (!$v_user) {
            return send_response(false, 'validation error!', ['email' => "this email doesn't exist!"],400);
        } elseif (!Hash::check($request->password, $v_user->password)) {
            return send_response(false, 'validation error!', ['password' => "wrong password!"],400);
        }

        //login prosses-------------------------------------------------
        if (auth()->attempt(array('email' => $input['email'], 'password' => $input['password']))) {
            if (auth()->user()->user_roles->where('role',2)->count() > 0) {
                if ((auth()->user()->company_roles->first()->role == 2) && auth()->user()->company_roles->first()->company->status == 1) {
                    $all_roles = auth()->user()->user_roles->where('role',2)->unique('company_code')->sortByDesc('last_login');
                    $company = null;
                    $c_id = '';
                    foreach ($all_roles as $role) {
                        if (!$company && $role->company->status == 1) {
                            $company = $role->company;
                            $c_id = $role->company->id;
                        }
                        $role->last_login = $role->company_code  == $c_id ? 1 : 0;
                        $role->save();
                    }
                    if ($company) {
                        return $this->admin_login_response($v_user, $company->id);
                    } else {
                        Auth::logout();
                        return send_response(false, 'sorry! your company has temporarily blocked!',[],400);
                    }
                } else {
                    $current_company_id = Auth::user()->company_roles->first()->company->id;

                    return $this->admin_login_response($v_user, $current_company_id);
                }
            } else {
                return send_response(false, 'you are not an admin!',[],400);
            }
        } else {
            return send_response(false, 'something went wrong!',[],400);
        }
    }

    #route for kiosk admin login response
    private function admin_login_response($v_user, $current_company_id)
    {
        $active_company_roles =  UserRole::where([
            ['user_id', Auth::id()],
            ['company_code', $current_company_id],
            ['role', 2]
        ])->orderBy('last_login', 'desc')->get();

        $user = Auth::user();

        $data['user']  = $v_user;
        $data['user']->mname  = Auth::user()->company->mname;
        $data['user']->lname  = Auth::user()->company->lname;

        $data['user']['current_company_code']  = $active_company_roles->first()->company->company_code;
        $data['user']['current_company']  = $active_company_roles->first()->company->id;
        $data['user']->token = $user->createToken('AccessToken')->accessToken;
        return send_response(true, 'You are successfully logged in.', $data);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return send_response(true, 'You are successfully logged out.');
    }

    public function forget_password(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);
        if ($validator->fails())
            return send_response(false, 'validation error!', $validator->errors(),400);

        try {
            $user = User::where('email', $request->email)->first();
            if (!$user)
                return send_response(false, 'validation error!', ['email'=>"we can't find this email"],400);

                // $token = Str::random(6);
                // $passwordReset = PasswordReset::updateOrCreate(
                // ['email' => $user->email],
                // [
                //     'email' => $user->email,
                //     'token' => $token
                // ]
            // );
            $token = Password::getRepository()->create($user);
            $user->sendPasswordResetNotification($token);
          
            // if ($user && $passwordReset)
            //         Mail::to($user->email)->send(new ForgetPassword($token, $user));
                
            return send_response(true, "password reset link sent to your email.");
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
    }

    public function reset_password(Request $request){
        $request->validate([
            'email' => 'required|email',
            'new_password' => 'required|min:8',
            'confirm_password' => 'required|min:8',
            'token' => 'required'
        ]);

        $passwordReset = PasswordReset::where([
            ['token', $request->token],
            ['email', $request->email],
            ['created_at','>=',Carbon::now()->subHours()]
        ])->first();

        if (!$passwordReset)
            return send_response(false, 'validation error!', ['token'=>"This password reset token is invalid."],400);

        $user = User::where('email', $passwordReset->email)->first();
        if (!$user)
        return send_response(false, 'validation error!', ['email'=>"we can't find this email"],400);

        if ($request->new_password === $request->confirm_password) {
            $user->password = bcrypt($request->new_password);
            $user->save();
            $passwordReset->delete();
            return send_response(true, "password successfully changed.");
        } else {
            return send_response(false, 'validation error!', ['confirm_password'=>"new password and confirm password doesn't matach"],400);
        }
        
    }

    public function update_firebase_token(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'token' => 'required',
                'device_type' => 'required',
            ]);
            if ($validator->fails())
                return send_response(false, 'validation error!', $validator->errors(),400);
            
            $user = FirebaseToken::where([
                ['user_id', Auth::id()],
                ['token', $request->token]
            ])->first();
            if (!$user){
                $user = new FirebaseToken;
                $user->user_id = Auth::id();
                $user->token = $request->token;
                $user->device_type = $request->device_type;
                $user->save();
            }else{
                $user->token = $request->token;
                $user->device_type = $request->device_type;
                $user->save();
            }
                
            return send_response(true, "token successfully updated.");
        } catch (\Throwable $e) {
            return send_response(false, $e->getMessage(), [],400);
        }
    }
}
