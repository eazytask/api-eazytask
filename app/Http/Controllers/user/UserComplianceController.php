<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use App\Http\Resources\user\UserComplianceResource;
use App\Models\UserCompliance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserComplianceController extends Controller
{
    public function index()
    {
        $comp = UserCompliance::where([
            ['user_id', Auth::id()]
        ])->get();
        return send_response(true, '', UserComplianceResource::collection($comp));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'compliance_id' => 'required',
            'certificate_no' => 'required',
            'expire_date' => 'required'
        ]);
        if ($validator->fails())
            return send_response(false, 'validation error!', $validator->errors(), 400);
        
        try {
            $exist_comp = UserCompliance::where([
                ['user_id', Auth::id()],
                ['compliance_id', $request->compliance_id]
            ])->first();

            if (!$exist_comp) {
                $user_compliance = new UserCompliance;
                $user_compliance->user_id = Auth::id();
                $user_compliance->email = Auth::user()->email;
                $user_compliance->compliance_id = $request->compliance_id;
                $user_compliance->certificate_no = $request->certificate_no;
                $user_compliance->comment = $request->comment;
                $user_compliance->expire_date = Carbon::parse($request->expire_date);

                $img = $request->image;
                $filename = null;
                if ($img) {

                    $folderPath = "images/compliance/";
                    $image_parts = explode(";base64,", $img);
                    $image_type_aux = explode("image/", $image_parts[0]);
                    $image_type = $image_type_aux[1];
                    $image_base64 = base64_decode($image_parts[1]);
                    $img_name = date('sihdmy') . '.' . $image_type;

                    $filename = $folderPath . $img_name;
                    if (file_exists($user_compliance->document)) {
                        unlink($user_compliance->document);
                    }
                    Image::make($image_base64)->save($filename);
                    $filename = $filename;
                }
                if ($filename) {
                    $user_compliance->document = $filename;
                }

                $exist_comp = $user_compliance->save();
            } else {
                // $exist_comp->id = $exist_comp->id;
                $exist_comp->certificate_no = $request->certificate_no;
                $exist_comp->comment = $request->comment;
                $exist_comp->expire_date = Carbon::parse($request->expire_date);

                $img = $request->image;
                $filename = null;
                if ($img) {

                    $folderPath = "images/compliance/";
                    $image_parts = explode(";base64,", $img);
                    $image_type_aux = explode("image/", $image_parts[0]);
                    $image_type = $image_type_aux[1];
                    $image_base64 = base64_decode($image_parts[1]);
                    $img_name = date('sihdmy') . '.' . $image_type;

                    $filename = $folderPath . $img_name;
                    if (file_exists($exist_comp->document)) {
                        unlink($exist_comp->document);
                    }
                    Image::make($image_base64)->save($filename);
                    $filename = $filename;
                }
                if ($filename) {
                    $exist_comp->document = $filename;
                }

                $exist_comp->save();
            }
            return send_response(true, 'compliance added successfully', new UserComplianceResource($exist_comp));
        } catch (\Throwable $e) {
            return send_response(false, $e->getMessage(), '', 422);
        }
    }

    public function distroy($id)
    {
        try {
            $exist_comp = UserCompliance::find($id);
            if ($exist_comp) {
                $exist_comp->delete();
                return send_response(true, 'Deleted successfully.');
            }
        } catch (\Throwable $e) {
            return send_response(false, $e->getMessage(), '', 422);
        }
        return send_response(false, 'invalid compliance id', '', 422);
    }
}
