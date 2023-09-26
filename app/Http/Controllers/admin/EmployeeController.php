<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\admin\EmployeeResource;
use App\Http\Resources\user\UserComplianceResource;
use App\Models\Employee;
use App\Models\User;
use App\Models\UserCompliance;
use App\Models\UserRole;
use App\Notifications\ExistingUserNotification;
// use App\Notifications\UserCredential;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Image;
use App\Mail\TestEmail;
use App\Mail\UserCredential;
use Illuminate\Support\Facades\Mail;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::where('company', Auth::user()->company_roles->first()->company->id)->orderBy('fname', 'asc')->get();
        return send_response(true, '', EmployeeResource::collection($employees));
    }

    //Employee Store
    public function store(Request $request)
    {
        $is_required = Auth::user()->company->company_type->id == 1 ? 'required' : '';
        $validator = Validator::make($request->all(), [
            'fname' => 'required',
            'lname' => 'required',
            'email' => 'required|email',
            'contact_number' => 'required',
            'role' => 'required',
            'status' => 'required',
            'address' => 'required',
            'suburb' => 'required',
            'postal_code' => 'required',
            'state' => 'required',
            'Compliance' => 'array',
            'license_no' => $is_required,
            'license_expire_date' => $is_required,
            'first_aid_license' => $is_required,
            'first_aid_expire_date' => $is_required,
        ]);
        if ($validator->fails())
            return send_response(false, 'validation error!', $validator->errors(), 400);

        try {
            $total_employee = Employee::where([
                ['email', $request->email],
                ['company', Auth::user()->company_roles->first()->company->id],
                ['role', $request->role]
            ])->count();

            if ($total_employee > 0) {
                return send_response(false, 'validation error!', ['email' => "sorry! this email is already used."], 400);
            }

            $image = $request->file;
            $filename = null;
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                if ($request->password) {
                    $password = $request->password;
                } else {
                    $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                    $password = substr(str_shuffle($chars), 0, 10);
                }
                $email_data['email'] = $request['email'];
                $email_data['name'] = $request['fname'];
                $email_data['mname'] = $request['mname'];
                $email_data['lname'] = $request['lname'];
                $email_data['password'] = $password;
                $email_data['company'] = Auth::user()->company->company;

                $user = new User;
                $user->name = $request->fname;
                $user->mname = $request->mname;
                $user->lname = $request->lname;
                $user->email = $request->email;
                $user->password = Hash::make($password);
                $user->save();
                $GLOBALS['data'] = $user;
                try {
                    $mail = Mail::to($user->email)->send(new UserCredential($email_data));
                    // $mail = $GLOBALS['data']->notify(new UserCredential($email_data));
                } catch (\Exception $e) {
                    // $GLOBALS['data']->delete();
                    send_response(false, 'validation error!', ['email' => "this email is incorrect."], 400);
                }
            } else {
                $GLOBALS['data'] = $user;
                try {
                    $mail = $GLOBALS['data']->notify(new ExistingUserNotification($request->name, Auth::user()->company->company));
                } catch (\Exception $e) {
                    return response()->json([
                        'message' => 'Sorry! this email is incorrect.',
                        'alertType' => 'warning'
                    ]);
                }
            }

            if ($image) {
                try {
                    $folderPath = "images/employees/";
                    $image_parts = explode(";base64,", $image);
                    $image_type_aux = explode("image/", $image_parts[0]);
                    $image_type = $image_type_aux[1];
                    $image_base64 = base64_decode($image_parts[1]);
                    $img_name = date('sihdmy') . '.' . $image_type;
                    $filename = $folderPath . $img_name;
                    Image::make($image_base64)->save($filename);
                } catch (\Throwable $e) {
                }
            }
            $employee = new Employee;
            $employee->user_id = Auth::user()->id;
            $employee->userID = $GLOBALS['data']->id;
            $employee->fname = $GLOBALS['data']->name;
            $employee->mname = $GLOBALS['data']->mname;
            $employee->lname = $GLOBALS['data']->lname;
            $employee->address = $request->address;
            $employee->suburb = $request->suburb;
            $employee->state = $request->state;
            $employee->postal_code = $request->postal_code;
            $employee->email = $request->email;
            $employee->contact_number = $request->contact_number;
            $employee->status = $request->status;

            function set_date($date = null)
            {
                return $date ? Carbon::parse($date)->toDateString() : null;
            }
            $employee->date_of_birth = set_date($request->date_of_birth);
            $employee->license_no = $request->license_no;
            $employee->license_expire_date = set_date($request->license_expire_date);
            $employee->first_aid_license = $request->first_aid_license;
            $employee->first_aid_expire_date = set_date($request->first_aid_expire_date);

            $employee->company = Auth::user()->company_roles->first()->company->id;
            $employee->role = $request->role;
            if ($filename) {
                $employee->image = $filename;
                $user = User::find($employee->userID);

                try {
                    unlink($user->image);
                } catch (\Throwable $e) {
                }

                $user->image = $filename;
                $user->save();
                DB::table('employees')->where('userID', $employee->userID)->update(array(
                    'image' => $filename,
                ));
            }

            if ($employee->save()) {

                #updating all employees
                $employees = Employee::where([
                    ['userID', $employee->userID],
                    ['role', $request->role]
                ])->get();
                foreach ($employees as $row) {
                    $row->fname = $request->fname;
                    $row->mname = $request->mname;
                    $row->lname = $request->lname;
                    $row->address = $request->address;
                    $row->suburb = $request->suburb;
                    $row->state = $request->state;
                    $row->status = $request->status;
                    $row->postal_code = $request->postal_code;
                    // $row->email = $request->email;
                    $row->contact_number = $request->contact_number;
                    $row->date_of_birth = set_date($request->date_of_birth);
                    $row->license_no = $request->license_no;
                    $row->license_expire_date = set_date($request->license_expire_date);
                    $row->first_aid_license = $request->first_aid_license;
                    $row->first_aid_expire_date = set_date($request->first_aid_expire_date);

                    if ($filename) {
                        $employee->image = $filename;

                        $user = User::find($employee->userID);
                        $user->image = $filename;
                        $user->save();
                        $row->image = $filename;
                    }
                    $row->save();
                }

                #adding role
                $user_role = new UserRole();
                $user_role->company_code = Auth::user()->company_roles->first()->company->id;
                $user_role->user_id = $employee->userID;
                $user_role->role = $request->role;
                if ($request->status == 1) {
                    $user_role->status = 1;
                } else {
                    $user_role->status = 0;
                }
                $user_role->sub_domain = Auth::user()->company_roles->first()->company->sub_domain ? 1 : 0;
                $user_role->save();


                if ($request->has_compliance) {
                    foreach ($request->Compliance as $compliance) {
                        $exist_comp = UserCompliance::where([
                            ['user_id', $employee->userID],
                            ['compliance_id', $compliance['compliance_id']]
                        ])->first();

                        if (!$exist_comp) {
                            $user_compliance = new UserCompliance;
                            $user_compliance->user_id = $employee->userID;
                            $user_compliance->email = $request->email;
                            $user_compliance->compliance_id = $compliance['compliance_id'];
                            $user_compliance->certificate_no = $compliance['certificate_no'];
                            $user_compliance->comment = $compliance['comment'];
                            $user_compliance->expire_date = Carbon::parse($compliance['expire_date']);
                            $user_compliance->save();
                        } else {
                            $exist_comp->certificate_no = $compliance['certificate_no'];
                            $exist_comp->comment = $compliance['comment'];
                            $exist_comp->expire_date = Carbon::parse($compliance['expire_date']);
                            $exist_comp->save();
                        }
                    }
                }
            }
            return send_response(true, 'employee added successfully', new EmployeeResource($employee));
        } catch (\Throwable $e) {
            return $e->getMessage();
            return send_response(true, $e->getMessage(), [], 400);
        }
    }

    public function update(Request $request)
    {
        $is_required = Auth::user()->company->company_type->id == 1 ? 'required' : '';
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'fname' => 'required',
            'lname' => 'required',
            'contact_number' => 'required',
            'role' => 'required',
            'status' => 'required',
            'address' => 'required',
            'suburb' => 'required',
            'postal_code' => 'required',
            'state' => 'required',
            'Compliance' => 'array',
            'license_no' => $is_required,
            'license_expire_date' => $is_required,
            'first_aid_license' => $is_required,
            'first_aid_expire_date' => $is_required,
        ]);
        if ($validator->fails())
            return send_response(false, 'validation error!', $validator->errors(), 400);

        $employee = Employee::find($request->id);
        if ($employee) {

            $emp_role = $employee->role;
            $employee->role = $request->role;

            $img = $request->file;
            $filename = null;
            if ($img) {
                try {
                    $folderPath = "images/employees/";
                    $image_parts = explode(";base64,", $img);
                    $image_type_aux = explode("image/", $image_parts[0]);
                    $image_type = $image_type_aux[1];
                    $image_base64 = base64_decode($image_parts[1]);
                    $img_name = date('sihdmy') . '.' . $image_type;

                    if (file_exists($employee->image)) {
                        unlink($employee->image);
                    }
                    $filename = $folderPath . $img_name;
                    Image::make($image_base64)->save($filename);
                } catch (\Throwable $e) {
                }
            }

            $employees = Employee::where([
                ['userID', $employee->userID],
                ['role', $request->role]
            ])->get();

            if (!$employees->count()) {
                $employees = Employee::where([
                    ['userID', $employee->userID],
                    ['role', $emp_role]
                ])->get();
            }

            function set_date($date = null)
            {
                return $date ? Carbon::parse($date)->toDateString() : null;
            }
            foreach ($employees as $row) {
                $row->fname = $request->fname;
                $row->mname = $request->mname;
                $row->lname = $request->lname;
                $row->address = $request->address;
                $row->suburb = $request->suburb;
                $row->state = $request->state;
                $row->status = $request->status;
                $row->postal_code = $request->postal_code;
                // $row->email = $request->email;
                $row->contact_number = $request->contact_number;
                $row->date_of_birth = set_date($request->date_of_birth);
                $row->license_no = $request->license_no;
                $row->license_expire_date = set_date($request->license_expire_date);
                $row->first_aid_license = $request->first_aid_license;
                $row->first_aid_expire_date = set_date($request->first_aid_expire_date);

                if ($filename) {
                    $employee->image = $filename;

                    $user = User::find($employee->userID);
                    $user->image = $filename;
                    $user->save();
                    $row->image = $filename;
                }
                $row->save();
            }

            if ($request->password) {
                User::findOrFail($employee->userID)->update([
                    'password' => Hash::make($request->password),
                ]);
            }

            $user_role = UserRole::where([
                ['user_id', $employee->userID],
                ['role', $emp_role],
                ['company_code', $employee->company]
            ])->first();
            if ($request->status == 1) {
                $user_role->status = 1;
            } else {
                $user_role->status = 0;
            }
            $user_role->role = $request->role;
            $user_role->save();

            if ($request->has_compliance && count($request->Compliance)) {
                foreach ($request->Compliance as $compliance) {
                    $exist_comp = UserCompliance::where([
                        ['user_id', $employee->userID],
                        ['compliance_id', $compliance['compliance_id']]
                    ])->first();

                    if (!$exist_comp) {
                        $user_compliance = new UserCompliance;
                        $user_compliance->user_id = $employee->userID;
                        $user_compliance->email = $request->email;
                        $user_compliance->compliance_id = $compliance['compliance_id'];
                        $user_compliance->certificate_no = $compliance['certificate_no'];
                        $user_compliance->comment = $compliance['comment'];
                        $user_compliance->expire_date = Carbon::parse($compliance['expire_date']);
                        $user_compliance->save();
                    } else {
                        $exist_comp->certificate_no = $compliance['certificate_no'];
                        $exist_comp->comment = $compliance['comment'];
                        $exist_comp->expire_date = Carbon::parse($compliance['expire_date']);
                        $exist_comp->save();
                    }
                }
            }
            $employee = Employee::find($request->id);
            return send_response(true, 'employee updated successfully', new EmployeeResource($employee));
        } else {
            return send_response(false, 'something went wrong!', [], 400);
        }
    }

    public function delete($id)
    {
        try {
            $employee = Employee::find($id);
            // if (!empty($employee->image)) {
            //     if (file_exists($employee->image)) {
            //         unlink($employee->image);
            //     }
            // }
            $user_id = $employee->userID;
            $emp_role = $employee->role;
            if ($employee->delete()) {
                $user_role = UserRole::where([
                    ['company_code', Auth::user()->company_roles->first()->company->id],
                    ['user_id', $user_id],
                    ['role', $emp_role],
                ])->first();
                if ($user_role) {
                    $user_role->delete();
                }
            }
            return send_response(true, 'employee deleted successfully.');
        } catch (\Throwable $e) {
            return send_response(false, 'sorry! this employee used somewhere.', [], 400);
        }
    }

    public function filter_compliance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);
        if ($validator->fails())
            return send_response(false, 'validation error!', $validator->errors(), 400);

        $compliances = UserCompliance::where('email', $request->email)->orderBy('id', 'desc')->get();
        $user = User::where('email', $request->email)->first();

        return send_response(true, '', ['compliances' => UserComplianceResource::collection($compliances), 'user' => $user]);
    }

    // public function compliances()
    // {
    //     $compliances = Compliance::all();
    //     return send_response(true, '', $compliances);
    // }
}
