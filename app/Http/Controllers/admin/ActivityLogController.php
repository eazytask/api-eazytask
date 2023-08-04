<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index()
    {
        $activity = Activity::where('log_name', auth()->user()->company_roles->first()->company_code)->latest('id')->get();
        return send_response(true, '', $activity);
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
            $fromRoaster = Carbon::parse($request->start_date);
            $toRoaster = Carbon::parse($request->end_date);

            $activity = Activity::where([
                ['log_name', auth()->user()->company_roles->first()->company_code],
            ])
                ->whereBetween('created_at', [$fromRoaster->startOfDay(), $toRoaster->endOfDay()])
                ->latest('id')->get();

            return send_response(true, '', $activity);
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
    }
}
