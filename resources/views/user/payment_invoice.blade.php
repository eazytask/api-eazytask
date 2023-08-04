@php
function getTime($date)
{
return \Carbon\Carbon::parse($date)->format('H:i');
}
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Invoice</title>
    <link rel="stylesheet" type="text/css" href="{{public_path('app-assets/css/bootstrap.css')}}">
    <link rel="stylesheet" type="text/css" href="{{public_path('app-assets/css/colors.css')}}">
    <style>
        body {
            font-size: 12px !important;
        }

        @page {
            margin: 15px;
        }
    </style>

</head>

<body>
    <!-- Invoice -->
    <div class="card invoice-preview-card">

        <table style="width: 100%;" class="mt-3">
            <!-- Header starts -->
            <td style="width: 35%; padding-right:3%" class="pl-3">

                <div style="text-align: -webkit-center;">
                    <div class="logo-wrapper">
                        <!-- <img src="{{asset('images/app/logo.png')}}" alt="" class="mb-50" height="36px"> -->
                        <img src="{{public_path('images/app/logo.png')}}" alt="" class="mb-50" width="100px">
                    </div>
                </div>
            </td>
            <td style="width: 26%;"></td>
            <td style="width: 35%; padding-right:3%" class="pl-3">

                <div style="text-align: -webkit-center;">
                    <span class="pr-1">Date Issued:</span>
                    <span class="font-weight-bolder ml-1 mr-2">
                        {{ \Carbon\Carbon::parse($payment->Payment_Date)->format('d-m-Y')}}
                    </span>
                </div>
            </td>
        </table>

        <hr class="invoice-spacing">

        <!-- Address and Contact starts -->
        <!-- <div class="card-body invoice-padding pb-0"> -->
        <!-- Header starts -->
        <!-- <div class="d-flex justify-content-between flex-md-row flex-column invoice-spacing mt-0"> -->

        <table style="width: 100%;">
            <td style="width: 35%; padding-right:3%" class="pl-3">

                <div class="logo-wrapper">
                    <h6 class="mb-2">Pay Slip:</h6>
                    <h6 class="mb-25">{{$payment->employee->fname}}{{$payment->employee->mname}}{{$payment->employee->lname}}</h6>
                    <p class="card-text mb-25">{{$payment->employee->address}}</p>
                    <p class="card-text mb-25">{{$payment->employee->state}}, {{$payment->employee->postal_code}}</p>
                    <p class="card-text mb-0">{{$payment->employee->contact_number}}</p>
                    <p class="card-text mb-0">{{$payment->employee->email}}</p>
                </div>
            </td>
            <td style="width: 24%"></td>
            <td style="width: 35%; padding-right:3%" class="pl-3">
                <div class="mt-md-0 mt-2">
                    <h6 class="mb-2">From:</h6>
                    <table>
                        <tbody>
                            <tr>
                                <td class="pr-1">Name:</td>
                                <td class="font-weight-bolder">
                                    {{$admin->name}}
                                </td>
                            </tr>
                            <tr>
                                <td class="pr-1">Company:</td>
                                <td class="font-weight-bolder">
                                    {{$payment->company->company}}
                                </td>
                            </tr>
                            <tr>
                                <td class="pr-1">Email:</td>
                                <td class="font-weight-bolder">
                                    {{$admin->email}}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </td>
        </table>
        <!-- </div> -->
        <!-- </div> -->
        <!-- Address and Contact ends -->

        <!-- Invoice Description starts -->
        <div class="table-responsive mt-3">
            <table class="table" id="test">
                <thead>
                    <tr class="bg-primary text-white">
                        <th class="p-1">#</th>
                        <th class="p-75">Roster Date</th>
                        <th class="p-75">site</th>
                        <th class="p-75">Shift start</th>
                        <th class="p-75">Shift end</th>
                        <th class="p-75">Clock In</th>
                        <th class="p-75">Clock Out</th>
                        <th class="p-75">Approved Start</th>
                        <th class="p-75">Approved End</th>
                        <th class="p-75">Duration</th>
                        <th class="p-75">Rate</th>
                        <th class="p-75">Amount</th>
                        <th class="p-75">Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($timekeepers as $k => $row)
                    @php

                    $app_start = getTime($row->Approved_start_datetime);
                    $app_end = getTime($row->Approved_end_datetime);

                    @endphp
                    <tr class="{{$k % 2 == 0?'bg-light-secondary':'bg-light-primary'}}">
                        <td class="p-1">{{$k +1}}</td>
                        <td class="pr-0 pl-0">{{ \Carbon\Carbon::parse($row->roaster_date)->format('d-m-Y')}}</td>
                        <td class="p-75">{{$row->project->pName}}</td>
                        <td class="p-75">{{getTime($row->shift_start)}}</td>
                        <td class="p-75">{{getTime($row->shift_end)}}</td>
                        <td class="p-75">{{$row->sing_in ? getTime($row->sing_in): 'none'}}</td>
                        <td class="p-75">{{$row->sing_out ? getTime($row->sing_out): 'none'}}</td>
                        <td class="p-75">{{$app_start}}</td>
                        <td class="p-75">{{$app_end}}</td>
                        <td class="p-75">{{$row->duration}}</td>
                        <td class="p-75">{{$row->ratePerHour}}</td>
                        <td class="p-75">{{$row->amount}}</td>
                        <td class="p-75">{{$row->remarks}}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <hr class="invoice-spacing" />

        <!-- <div class="card-body invoice-padding pb-0">
            <div class="row invoice-sales-total-wrapper">
                <div class="col-md-12 d-flex justify-content-end order-md-2 order-1"> -->
                    <div class="ml-auto" style="width: 211px;">
                        <div class="invoice-total-item">
                            <p class="invoice-total-title mb-50">Duration: <span class="float-lg-right font-weight-bolder" id="total-duration">{{$payment->details->total_hours}} Hours</span></p>
                            <p class="invoice-total-title mb-50">Sub-Total: <span class="float-lg-right font-weight-bolder" id="total-duration">${{$payment->details->total_pay - $payment->details->additional_pay}}</span></p>
                            <p class="invoice-total-title mb-50">Additional: <span class="float-lg-right font-weight-bolder" id="total-duration">${{$payment->details->additional_pay}}</span></p>
                        </div>
                        <hr class="my-50">
                        <div class="invoice-total-item">
                            <p class="invoice-total-title mb-50">Total: <span class="float-lg-right font-weight-bolder" id="total-amount">${{$payment->details->total_pay}}</span></p>
                            <p class="invoice-total-title mb-50">Payment Method: <span class="float-lg-right font-weight-bolder" id="total-amount">{{$payment->details->PaymentMethod}}</span></p>
                        </div>
                    </div>
                <!-- </div>
            </div>
        </div> -->

        <hr class="invoice-spacing" />

        <div class="row">
            <div class="col-sm-12">
                <div class="form-group mb-2 pl-2">
                    <label for="note" class="form-label font-weight-bold"></label>
                    <span>{{$payment->details->Remarks}}</span>
                </div>
            </div>
        </div>
    </div>
    <!-- /Invoice -->
</body>

</html>