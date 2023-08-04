@component('mail::message')
# Hello  {{ucwords($user->name)}},

You are receiving this email because we received a password reset request for your account.
Don't share this code.

@component('mail::button', ['url' => $url])
Reset Password
@endcomponent

This password reset link will expire in 60 minutes.
If you did not request a password reset, no further action is required.

---
Thanks,<br>
{{ config('app.name') }}

@endcomponent
