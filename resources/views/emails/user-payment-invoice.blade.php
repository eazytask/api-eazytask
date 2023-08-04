@component('mail::message')
# Hello {{ucwords($user->fname)}},

A new payment has added for you. Please Save your document carefully.


@component('mail::button', ['url' => 'https://eazytask.au/admin/home/payment/list/'. $payment_id .'/'. $user->company])
Download Now
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent