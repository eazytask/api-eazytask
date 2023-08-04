@component('mail::message')
# Hello  {{ucwords($user->fname)}},

New scheduled shift published for you for week ending {{\Carbon\Carbon::parse($data->first()->roaster_date)->endOfWeek()->format('d-m-Y')}}. Please log on to eazytask to accept / declined it.

@foreach($data as $roster)
**{{$roster->project->pName}}**, {{\Carbon\Carbon::parse($roster->roaster_date)->format('d/m/Y')}}, {{\Carbon\Carbon::parse($roster->shift_start)->format('H:i')}}-{{\Carbon\Carbon::parse($roster->shift_end)->format('H:i')}}.  
@endforeach

@component('mail::button', ['url' => 'https://eazytask.au/home/unconfirmed/shift'])
Confirm Now
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
