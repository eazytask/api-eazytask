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
    <title>Roster Report</title>
    <link rel="stylesheet" type="text/css" href="{{public_path('app-assets/css/bootstrap.css')}}">
    <link rel="stylesheet" type="text/css" href="{{public_path('app-assets/css/colors.css')}}">
    <!-- <link rel="stylesheet" type="text/css" href="{{asset('app-assets/css/bootstrap.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/css/colors.css')}}"> -->
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
    <div class="border border-primary">
        <div class="bg-primary p-1 m-50 rounded">
            <table style="width: 100%;">
                <th class="text-center" style="width: 33%;">
                    <p class="h6 text-light" style="color: white;">Roster Report</p>
                </th>
                <th class="text-center" style="width: 33%;">
                    <p class="h6 text-light">Total Hours: {{(double) $timekeepers->sum('duration')}}</p>
                </th>
                <th class="text-center" style="width: 33%;">
                    <p class="h6 text-light">Total Amount: ${{(double) $timekeepers->sum('amount')}}</p>
                </th>
            </table>
        </div>
        <table class="table table-striped">

            <tbody>
                <tr class="">
                    <th>#</th>
                    <th>Venue</th>
                    <th>Roster Date</th>
                    <th>Shift start</th>
                    <th>Shift end</th>
                    <th>Clock In</th>
                    <th>Clock Out</th>
                    <th>Duration</th>
                    <th>Rate</th>
                    <th>Amount</th>
                </tr>
                @foreach ($timekeepers as $k => $row)
                <tr>
                    <td>{{ $k + 1 }}</td>
                    <td>
                        @if (isset($row->project->pName))
                        {{ $row->project->pName }}
                        @else
                        Null
                        @endif
                    </td>
                    <td>
                        {{ \Carbon\Carbon::parse($row->roaster_date)->format('d-m-Y')}}
                    </td>
                    <td>{{getTime($row->shift_start)}}</td>
                    <td>{{getTime($row->shift_end)}}</td>
                    <td>{{$row->sing_in ? getTime($row->sing_in): 'none'}}</td>
                    <td>{{$row->sing_out ? getTime($row->sing_out): 'none'}}</td>
                    <td>{{ $row->duration }}</td>
                    <td>{{ $row->ratePerHour }}</td>
                    <td>{{ $row->amount }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    </div>
    </div>
</body>

</html>