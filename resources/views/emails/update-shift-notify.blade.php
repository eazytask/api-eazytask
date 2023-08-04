@component('mail::message')
# Hello {{ucwords($user->fname)}},

one of your shift {{$roster->project->pName}} week ending {{\Carbon\Carbon::parse($roster->roaster_date)->endOfWeek()->format('d-m-Y')}} has been {{$ext}}. Please check eazytask for changes.

**{{$roster->project->pName}}**, {{\Carbon\Carbon::parse($roster->roaster_date)->format('d/m/Y')}}, {{\Carbon\Carbon::parse($roster->shift_start)->format('H:i')}}-{{\Carbon\Carbon::parse($roster->shift_end)->format('H:i')}}.  

@component('mail::button', ['url' => 'https://eazytask.au/home/upcoming/shift'])
Confirm Now
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
