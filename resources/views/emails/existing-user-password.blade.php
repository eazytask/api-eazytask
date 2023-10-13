@component('mail::message')
    # Dear {{ ucwords($name) }},

    Congratulations!
    Your account has been setup and you are ready to explore Eazytask features from
    {{ strtoupper($company) }}.

    @component('mail::button', ['url' => 'https://eazytask.au'])
        Login Now
    @endcomponent

    @component('mail::panel')
        [![Download on the App
        Store](https://w7.pngwing.com/pngs/270/658/png-transparent-app-store-apple-google-play-apple-text-logo-mobile-phones.png)](https://apps.apple.com/id/app/eazytask/id1642332032)
        [![Get it on Google
        Play](https://play.google.com/intl/en_us/badges/static/images/badges/en_badge_web_generic.png)](https://play.google.com/store/apps/details?id=com.ni.Easytask)
    @endcomponent

    Thank you for using our application!
    If you need any further assistance, please contact our support team

    ---
    Thanks,<br>
    {{ config('app.name') }}
@endcomponent
