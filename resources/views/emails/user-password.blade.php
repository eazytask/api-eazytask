@component('mail::message')
    # Dear {{ ucwords($name) }},

    Congratulations!
    Your account has been setup and you are ready to explore Eazytask features from
    {{ strtoupper($company) }}.
@endcomponent
