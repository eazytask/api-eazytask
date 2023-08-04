@component('mail::message')
# Hello {{ucwords($user->fname)}},


**{{$msg}}**

If you interested please send event request.

@component('mail::button', ['url' => 'https://eazytask.au/user/home/upcomingevent/go'])
interested
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
