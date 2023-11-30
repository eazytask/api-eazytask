<?php

namespace App\Http\Controllers;

use App\Http\Resources\user\UserRoleResource;
use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Image;

class ProfileController extends Controller
{
    public function notifications()
    {
        $all_notifications = Auth::user()->notifications;
        $notifications = [];
        foreach ($all_notifications as $row) {
            $noti = [];
            $noti['type'] = $row->data['type'];
            $noti['status'] = $row->data['status'];
            $noti['msg'] = $row->data['msg'];
            $noti['image'] = optional($row->sender)->image ? asset(optional($row->sender)->image) : '';
            $noti['created_at'] = Carbon::parse($row->created_at)->diffForHumans();
            $noti['is_read'] = $row->read_at ? true : false;

            array_push($notifications, $noti);
        }
        // $notifications = [
        //     "unread_notification" => Auth::user()->unreadNotifications,
        //     "total_unread_notification" => Auth::user()->unreadNotifications->count(),
        //     "all_notification" => Auth::user()->notifications,
        // ];
        return send_response(true, '', $notifications);
    }

    public function read_notifications()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return send_response(true, 'successfully marked as read');
    }

    public function delete_notifications()
    {
        $user = Auth::user();
        $user->notifications()->delete();
        return send_response(true, 'notifications successfully cleared');
    }


    public function profile()
    {
        $user = (object)[];
        $user->id = Auth::id();
        $user->name = Auth::user()->name;
        $user->mname  = Auth::user()->mname;
        $user->lname  = Auth::user()->lname;
        // if(Auth::user()->company_roles->first()->role == 3){
        //     $user->mname  = Auth::user()->employee->mname;
        //     $user->lname  = Auth::user()->employee->lname;
        // }elseif(Auth::user()->company_roles->first()->role == 4){
        //     $user->mname  = Auth::user()->supervisor->mname;
        //     $user->lname  = Auth::user()->supervisor->lname;
        // }else{
        //     $user->mname  = Auth::user()->company->mname;
        //     $user->lname  = Auth::user()->company->lname;
        // }

        $all_roles = [];
        foreach (Auth::user()->company_roles as $role) {
            array_push($all_roles, $role->role);
        }
        $user->image = Auth::user()->image;
        $user->email = Auth::user()->email;
        $user->pin = Auth::user()->pin;
        $user->current_company = Auth::user()->company_roles->sortByDesc('last_login')->first()->company->id;
        $user->current_company_code = Auth::user()->company_roles->sortByDesc('last_login')->first()->company->company_code;
        $user->current_role_id  = Auth::user()->company_roles->sortByDesc('last_login')->first()->id;
        $user->current_role  = Auth::user()->company_roles->sortByDesc('last_login')->first()->role;
        $user->roles  = $all_roles;
        
        $query = Employee::select('license_no', 'contact_number', 'address', 'suburb', 'state', 'postal_code')->where('company', $user->current_company)
                            ->where('userID', $user->id)->first();

        $user->license_no = $query->license_no;
        $user->contact_number = $query->contact_number;
        $user->address = $query->address;
        $user->suburb = $query->suburb;
        $user->state = $query->state;
        $user->postal_code = $query->postal_code;
        return send_response(true, '', $user);
    }

    public function profile_update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address' => 'required',
            'suburb' => 'required',
            'state' => 'required',
            'postal_code' => 'required',
            'contact_number' => 'required',
        ]);
        if ($validator->fails())
            return send_response(false, 'validation error!', $validator->errors(), 400);

        try {
            // $admin = User::find(Auth::id());
            // $admin->name = $request->name;
            // $admin->mname = $request->mname;
            // $admin->lname = $request->lname;
            // $admin->save();

            // if ($request->company) {
            //     $company = Auth::user()->company;
            //     $company->company = $request->company;
            //     $company->company_contact = $request->company_contact;
            //     $company->save();
            // }
            // $companies = Company::where('user_id', Auth::id())->get();
            // foreach ($companies as $row) {
            //     $row->mname = $request->mname;
            //     $row->lname = $request->lname;
            //     $row->save();
            // }

            $employees = Employee::where('userID', Auth::id())->get();
            foreach ($employees as $row) {
                $row->address = $request->address;
                $row->suburb = $request->suburb;
                $row->state = $request->state;
                $row->postal_code = $request->postal_code;
                $row->contact_number = $request->contact_number;
                $row->save();
            }

            return send_response(true, 'profile updated successfully.');
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
    }

    public function change_password(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
            'new_password' => 'required|min:8',
            'confirm_password' => 'required|min:8',
        ]);
        if ($validator->fails())
            return send_response(false, 'validation error!', $validator->errors(), 400);

        $db_pass = Auth::user()->password;
        $current_password = $request->old_password;
        $newpass = $request->new_password;
        $confirmpass = $request->confirm_password;

        if (Hash::check($current_password, $db_pass)) {
            if ($newpass === $confirmpass) {
                User::findOrFail(Auth::id())->update([
                    'password' => Hash::make($newpass)
                ]);

                return send_response(true, "password changed successfully.");
            } else {
                // return send_response(false, "new password and confirm password doesn't matach.",400);
                return send_response(false, 'validation error!', ['confirm_password' => "new password and confirm password doesn't matach"], 400);
            }
        } else {
            // return send_response(false, "old password doesn't matach.",400);
            return send_response(false, 'validation error!', ['old_password' => "old password doesn't matach."], 400);
        }
    }

    public function image_update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required',
        ]);
        if ($validator->fails())
            return send_response(false, 'validation error!', $validator->errors(), 400);
        try {
            $user = User::find(Auth::id());
            $img = $request->file;
            $filename = null;
            if ($img) {

                $folderPath = "images/profile/";
                $image_parts = explode(";base64,", $img);
                $image_type_aux = explode("image/", $image_parts[0]);
                $image_type = $image_type_aux[1];
                $image_base64 = base64_decode($image_parts[1]);
                $img_name = date('sihdmy') . '.' . $image_type;

                $filename = $folderPath . $img_name;
                if (file_exists($user->image)) {
                    unlink($user->image);
                }
                Image::make($image_base64)->save($filename);
                $filename = $filename;
            }
            if ($filename) {
                $user->image = $filename;
                DB::table('employees')->where('userID', Auth::id())->update(array(
                    'image' => $filename,
                ));
            }
            $user->save();
            return send_response(true, 'photo updated successfully');
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
    }

    //only for user
    public function change_pin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|min:8',
            'new_pin' => 'required|min:4|max:4',
            'confirm_pin' => 'required|min:4|max:4',
        ]);
        if ($validator->fails())
            return send_response(false, 'validation error!', $validator->errors(), 400);

        $db_pass = Auth::user()->password;
        $current_password = $request->old_password;
        $newpass = $request->new_pin;
        $confirmpass = $request->confirm_pin;

        if (Hash::check($current_password, $db_pass)) {
            if ($newpass === $confirmpass) {
                $user = User::find(Auth::id());
                $user->pin = $newpass;

                if ($user->save()) {
                    return send_response(true, "your pin changed successfully");
                }
            } else {
                // return send_response(false, "new pin and confirm pin doesn't matach.",400);
                return send_response(false, 'validation error!', ['confirm_pin' => ["new pin and confirm pin doesn't matach."]], 400);
            }
        } else {
            // return send_response(false, "old password doesn't matach.",400);
            return send_response(false, 'validation error!', ['old_password' => ["old password doesn't matach."]], 400);
        }
    }
}
