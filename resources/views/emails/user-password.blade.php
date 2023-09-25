@component('mail::message')
    # Dear {{ ucwords($name) }},

    Congratulations!
    Your account has been setup and you are ready to explore Eazytask features from
    {{ strtoupper($company) }}.

    Please don't share your credentials with anyone and after login change your password. Your
    account temporary login credential below-

    @component('mail::button', ['url' => 'https://eazytask.au'])
        Login Now
    @endcomponent

    Thank you for using our application!
    If you need any further assistance, please contact our support team

    ---
    Thanks,<br>
    {{ config('app.name') }}
@endcomponent
