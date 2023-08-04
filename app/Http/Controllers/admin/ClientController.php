<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\admin\ClientRecource;
use Illuminate\Http\Request;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Image;

class ClientController extends Controller
{
    //Employee View File
    public function index()
    {
        $clients = Client::where('company_code', Auth::user()->company_roles->first()->company->id)->orderBy('cName', 'asc')->get();
        return send_response(true, '',ClientRecource::collection($clients));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cname' => 'required',
            'cemail' => 'required',
            'cnumber' => 'required',
            'cperson' => 'required',
            'status' => 'required',
            'caddress' => 'required',
            'suburb' => 'required',
            'cpostal_code' => 'required',
            'cstate' => 'required',
        ]);
        if ($validator->fails())
            return send_response(false, 'validation error!', $validator->errors(), 400);

        try {
            $client = new Client;
            $client->user_id = Auth::id();
            $client->cname = $request->cname;
            $client->cemail = $request->cemail;
            $client->cnumber = $request->cnumber;
            $client->caddress = $request->caddress;
            $client->suburb = $request->suburb;
            $client->cstate = $request->cstate;
            $client->status = $request->status;
            $client->cpostal_code = $request->cpostal_code;
            $client->cperson = $request->cperson;
            $client->company_code = Auth::user()->company_roles->first()->company->id;

            $image = $request->file;
            $filename = null;
            if ($image) {
                try {
                    $folderPath = "images/clients/";
                    $image_parts = explode(";base64,", $image);
                    $image_type_aux = explode("image/", $image_parts[0]);
                    $image_type = $image_type_aux[1];
                    $image_base64 = base64_decode($image_parts[1]);
                    $img_name = date('sihdmy') . $image_type;
                    $filename = $folderPath . $img_name;
                    Image::make($image_base64)->save($filename);
                } catch (\Throwable $e) {
                }
            }
            $client->cimage = $filename;
            $client->save();
            return send_response(true, 'client added successfully', new ClientRecource($client));
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
    }
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'cname' => 'required',
            'cemail' => 'required',
            'cnumber' => 'required',
            'cperson' => 'required',
            'status' => 'required',
            'caddress' => 'required',
            'suburb' => 'required',
            'cpostal_code' => 'required',
            'cstate' => 'required',
        ]);
        if ($validator->fails())
            return send_response(false, 'validation error!', $validator->errors(), 400);
        try {
            $client = Client::find($request->id);
            $client->user_id = Auth::id();
            $client->cname = $request->cname;
            $client->cemail = $request->cemail;
            $client->cnumber = $request->cnumber;
            $client->caddress = $request->caddress;
            $client->suburb = $request->suburb;
            $client->cstate = $request->cstate;
            $client->status = $request->status;
            $client->cpostal_code = $request->cpostal_code;
            $client->cperson = $request->cperson;

            $img = $request->file;
            $filename = null;
            if ($img) {
                try {
                    $folderPath = "images/clients/";
                    $image_parts = explode(";base64,", $img);
                    $image_type_aux = explode("image/", $image_parts[0]);
                    $image_type = $image_type_aux[1];
                    $image_base64 = base64_decode($image_parts[1]);
                    $img_name = date('sihdmy') . '.' . $image_type;

                    $filename = $folderPath . $img_name;
                    if (file_exists($client->cimage)) {
                        unlink($client->cimage);
                    }
                    Image::make($image_base64)->save($filename);
                    $filename = $filename;
                } catch (\Throwable $e) {
                }
            }
            if ($filename) {
                $client->cimage = $filename;
            }
            $client->save();
            return send_response(true, 'client updated successfully', new ClientRecource($client));
        } catch (\Throwable $e) {
            // return $e->getMessage();
            return send_response(false, 'client not found!',null,404);
        }
    }
    public function delete($id)
    {
        try {
            $client = Client::find($id);
            if (!empty($client->cimage)) {
                if (file_exists($client->cimage)) {
                    unlink($client->cimage);
                }
            }
            $client->delete();
            return send_response(true, 'client deleted successfully');
        } catch (\Throwable $e) {
            return send_response(false, 'sorry! this client used somewhere',[], 400);
        }
    }
}
