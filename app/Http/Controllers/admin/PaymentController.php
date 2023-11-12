<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\user\UserTimekeeperResource;
use App\Models\paymentdetails;
use App\Models\paymentmaster;
use App\Models\TimeKeeper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $fromDate = $request->start_date ? $request->start_date : Carbon::now()->startOfYear();
        $toDate = $request->end_date ? $request->end_date : Carbon::now();
        $filter_project = $request->project_id ? ['project_id', $request->project_id] : ['employee_id', '>', 0];
        $filter_employee = $request->employee_id ? ['employee_id', $request->employee_id] : ['employee_id', '>', 0];

        // $employees = DB::table('time_keepers')
        //     ->select(
        //         'time_keepers.id as id',
        //         DB::raw(
        //             'e.id as employee_id,
        //             e.fname,
        //             e.mname,
        //             e.lname,
        //             sum(time_keepers.duration) as total_hours,
        //             sum(time_keepers.amount) as total_amount'
        //         )
        //     )
        //     ->leftJoin('employees as e', 'e.id', 'time_keepers.employee_id')
        //     ->where([
        //         ['e.company', Auth::user()->company_roles->first()->company->id],
        //         ['e.role', 3]
        //     ])
        //     ->groupBy("e.id")
        //     ->orderBy('e.fname', 'asc')
        //     ->whereBetween('roaster_date', [Carbon::parse($fromDate)->toDateString(), Carbon::parse($toDate)->toDateString()])
        //     ->where([
        //         ['payment_status', 0],
        //         // ['sing_out','!=',null],
        //         $filter_project,
        //         $filter_employee
        //     ])
        //     ->where(function ($q) {
        //         avoid_rejected_key($q);
        //     })
        //     ->get();
        
        $payments = paymentmaster::whereBetween('Payment_Date', [Carbon::parse($fromDate)->toDateString(), Carbon::parse($toDate)->toDateString()])
            ->where([
                ['user_id',Auth::id()],
                $filter_employee
            ])
            ->orderBy('Payment_Date','desc')
            ->get();

        // Now you can loop through $payments and access total_hours
        foreach ($payments as $payment) {
            // Accessing total_hours for each paymentmaster
            $payment->total_hours = $payment->details->total_hours;
            $payment->total_amount = $payment->details->total_pay;
            $payment->fname = $payment->employee->fname;
            $payment->mname = $payment->employee->mname;
            $payment->lname = $payment->employee->lname;

            // Do something with $total_hours
        }

        return send_response(true, '', $payments);
    }

    public function get_rosters(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'employee_id' => 'required',
            ]);
            if ($validator->fails())
                return send_response(false, 'validation error!', $validator->errors(), 400);

            $fromDate = $request->start_date ? Carbon::parse($request->start_date)->format('Y-m-d') : Carbon::now()->startOfYear();
            $toDate = $request->end_date ? Carbon::parse($request->end_date)->format('Y-m-d') : Carbon::now();
            $filter_project = $request->project_id ? ['project_id', $request->project_id] : ['employee_id', '>', 0];

            // $rosters = TimeKeeper::where([
            //     ['employee_id', $request->employee_id],
            //     // ['company_code', Auth::user()->company_roles->first()->company->id],
            //     ['payment_status', '=', 0],
            //     $filter_project,
            // ])
            //     ->orderBy('roaster_date', 'asc')
            //     ->orderBy('shift_start', 'asc')
            //     ->whereBetween('roaster_date', [$fromDate, $toDate])
            //     ->where(function ($q) {
            //         avoid_rejected_key($q);
            //     })
            //     ->get();

            // $rosters = DB::table('time_keepers')
            //     ->select(DB::raw(
            //         'e.* ,
            //         e.fname as name,
            //         sum(time_keepers.duration) as total_hours,
            //         sum(time_keepers.amount) as total_amount ,
            //         count(time_keepers.id) as record'
    
            //     ))
            //     ->leftJoin('employees as e', 'e.id', 'time_keepers.employee_id')
            //     ->where([
            //         ['employee_id', $request->employee_id],
            //         ['e.company', Auth::user()->company_roles->first()->company->id],
            //         ['e.role', 3]
            //     ])
            //     ->groupBy("e.id")
            //     ->orderBy('e.fname', 'asc')
            //     ->whereBetween('roaster_date', [$fromDate, $toDate])
            //     ->where([
            //         ['payment_status', 0],
            //         // ['sing_out','!=',null],
            //         $filter_project
            //     ])
            //     ->where(function ($q) {
            //         avoid_rejected_key($q);
            //     })
            //     ->get();

            $query = TimeKeeper::where([
                    ['employee_id', $request->employee_id],
                    ['time_keepers.company_code', Auth::user()->company_roles->first()->company->id],
                    ['payment_status', 0],
                    $filter_project,
                    ])
                    ->leftJoin('employees as e', 'e.id', 'time_keepers.employee_id')
                    ->leftJoin('projects as p', 'p.id', 'time_keepers.project_id')
                    ->orderBy('roaster_date','asc')
                    ->orderBy('shift_start','asc')
                    ->whereBetween('roaster_date', [$fromDate, $toDate])
                    ->where(function ($q) {
                    avoid_rejected_key($q);
                    })
                    ->select('time_keepers.*', 'p.pName as project_name', 'e.fname', 'e.mname', 'e.lname');

            $rosters = $query->get();
            // $total_hours = $query->sum('duration');
            // $total_amount = $query->sum('amount');
            // $rosters->total_hours = $total_hours;
            // dd($rosters->total_hours);

            return send_response(true, '', $rosters);
        } catch (\Throwable $e) {
            return send_response(false, 'something went wrong!', 400);
        }
    }

    // public function update_rosters(Request $request)
    // {
    //     try {
    //         $validator = Validator::make($request->all(), [
    //             'rosters' => 'required|array',
    //         ]);
    //         if ($validator->fails())
    //             return send_response(false, 'validation error!', $validator->errors(), 400);

    //         foreach ($request->rosters as $roster) {
    //             $roster = (object)$roster;

    //             $timekeeper = TimeKeeper::find($roster->id);
    //             // $roster->roaster_date =  Carbon::parse($roster->roaster_date)->format('d-m-Y');
    //             $shift_start = Carbon::parse($timekeeper->roaster_date . $roster->shift_start);
    //             // $shift_end = Carbon::parse($roster->roaster_date . $roster->shift_end);
    //             $shift_end = Carbon::parse($shift_start)->addMinute($roster->duration * 60);

    //             $total_hour = $shift_start->floatDiffInRealHours($shift_end);
    //             $duration = round($total_hour, 2);
    //             $rate = $roster->ratePerHour;
    //             $errors = [];
    //             if ($duration * $rate != $roster->amount) {
    //                 $errors['amount'] = 'amount is not correct!';
    //             }
    //             if (count($errors)) {
    //                 return send_response(false, 'validation error!', $errors, 400);
    //             }

    //             $timekeeper->shift_start = $shift_start;
    //             $timekeeper->shift_end = $shift_end;
    //             $timekeeper->duration = $duration;
    //             $timekeeper->ratePerHour = $rate;
    //             $timekeeper->amount = $duration * $rate;
    //             $timekeeper->updated_at = Carbon::now();
    //             $timekeeper->save();
    //         }
    //         return send_response(true, 'rosters updated successfully');
    //     } catch (\Throwable $e) {
    //         return send_response(true, $e->getMessage(), 400);
    //     }
    // }

    public function add_payment(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'pay_date' => 'required',
                'employee_id' => 'required',
                'comment' => 'required',
                'payment_method' => 'required',
                'timekeeper_ids' => 'required|array',
            ]);
            if ($validator->fails())
                return send_response(false, 'validation error!', $validator->errors(), 400);

            $paymentmaster = paymentmaster::create([
                'Payment_Date' => Carbon::parse($request->pay_date)->toDateString(),
                'User_ID' => Auth::id(),
                'employee_id' => $request->employee_id,
                'Company_Code' => Auth::user()->company_roles->first()->company->id,
                'Comments' => $request->comment,
                'ExtraDsscription' => ''
            ]);

            $additional_pay = $request->additional_pay ? $request->additional_pay : 0;
            TimeKeeper::whereIn('id', $request->timekeeper_ids)->update(['payment_status' => 1]);
            $timekeepers = TimeKeeper::whereIn('id', $request->timekeeper_ids)->get();
            $total_hour = $timekeepers->sum('app_duration');
            $total_amount = $timekeepers->sum('app_amount') + $additional_pay;
            
            $paymentdetails = new paymentdetails;
            $paymentdetails->payment_master_id  = $paymentmaster->id;
            $paymentdetails->timekeeper_ids = serialize($request->timekeeper_ids);
            $paymentdetails->additional_pay = $additional_pay;
            $paymentdetails->total_pay = $total_amount;
            $paymentdetails->total_hours = $total_hour;
            $paymentdetails->Remarks = $request->comment;
            $paymentdetails->PaymentMethod = $request->payment_method;
            $paymentdetails->save();
            
            return send_response(true, 'payment successfully added',[]);
        } catch (\Throwable $e) {
            return send_response(false, 'something went wrong!', 400);
        }
    }
}
