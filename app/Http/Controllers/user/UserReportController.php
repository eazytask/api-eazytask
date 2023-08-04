<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use App\Http\Resources\user\PaymentResource;
use App\Http\Resources\user\UserTimekeeperResource;
use App\Models\paymentmaster;
use App\Models\TimeKeeper;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use PDF;

class UserReportController extends Controller
{
    #roster report
    // public function roster_report(Request $request)
    // {
    //     $start_month = Carbon::now()->subMonths();
    //     $end_month = Carbon::now()->endOfMonth();

    //     $start_date = $request->start_date ? Carbon::parse($request->start_date) : $start_month;
    //     $end_date = $request->end_date ? Carbon::parse($request->end_date) : $end_month;
        
    //     $project_id = $request->project_id;
    //     $payment_status = $request->payment_status;

    //     $filter_payment = $payment_status != '' ? ['payment_status', $payment_status] : ['employee_id', '>', 0];
    //     $filter_project = $project_id ? ['project_id', $project_id] : ['employee_id', '>', 0];

    //     $timekeepers = TimeKeeper::where([
    //         ['employee_id', Auth::user()->employee->id],
    //         ['company_code', Auth::user()->employee->company],
    //         // ['sing_in', '!=', null]
    //         $filter_payment,
    //         $filter_project
    //     ])
    //     ->where(function ($q) {
    //         avoid_rejected_key($q);
    //     })
    //     ->whereBetween('roaster_date', [$start_date, $end_date])
    //     ->orderBy('roaster_date', 'desc')
    //     ->get();
        
    //     return send_response(true, '', UserTimekeeperResource::collection($timekeepers));
    // }

    public function download_roster_report(Request $request){
        $start_month = Carbon::now()->subMonths();
        $end_month = Carbon::now()->endOfMonth();

        $start_date = $request->start_date ? Carbon::parse($request->start_date) : $start_month;
        $end_date = $request->end_date ? Carbon::parse($request->end_date) : $end_month;
        
        $project_id = $request->project_id;
        $payment_status = $request->payment_status;

        $filter_payment = $payment_status != '' ? ['payment_status', $payment_status] : ['employee_id', '>', 0];
        $filter_project = $project_id ? ['project_id', $project_id] : ['employee_id', '>', 0];

        $timekeepers = TimeKeeper::where([
            ['employee_id', 5],
            ['company_code', 3],
            // ['sing_in', '!=', null]
            $filter_payment,
            $filter_project
        ])
        ->where(function ($q) {
            avoid_rejected_key($q);
        })
        ->whereBetween('roaster_date', [$start_date, $end_date])
        ->orderBy('roaster_date', 'desc')
        ->get();
        // return $timekeepers;
        // return view('user.roster_report',compact('timekeepers'));
        $data = [
            'timekeepers' => $timekeepers,
        ];
        $pdf = PDF::loadView('user.roster_report', $data);

        return $pdf->download('roster-report.pdf');
        // return $pdf->stream();
    }

    #payment report
    public function payment_reports(Request $request)
    {
        $start_month = Carbon::now()->subMonths();
        $end_month = Carbon::now()->endOfMonth();

        $start_date = $request->start_date ? Carbon::parse($request->start_date) : $start_month;
        $end_date = $request->end_date ? Carbon::parse($request->end_date) : $end_month;

        $payments = paymentmaster::whereBetween('Payment_Date', [$start_date, $end_date])
            ->where([
                ['employee_id', Auth::user()->employee->id],
            ])
            ->get();
        return send_response(true, '', PaymentResource::collection($payments));
    }

    public function download_payment_invoice($id)
    {
        set_time_limit(300);
        $payment = paymentmaster::find($id);
        if ($payment) {
            $admin = User::find($payment->User_ID);
            $timekeepers = TimeKeeper::whereIn('id', unserialize($payment->details->timekeeper_ids))->get();
            // return view('user.payment_invoice',compact('payment','timekeepers','admin'));
            $data = [
                'admin' => $admin,
                'timekeepers' => $timekeepers,
                'payment' => $payment,
            ];
            $pdf = PDF::loadView('user.payment_invoice', $data);
    
            return $pdf->download('payment-invoice.pdf');
            // return $pdf->stream();
        
        } else {
            return send_response(false, 'invalid payment id', 400);
        }
    }
}
