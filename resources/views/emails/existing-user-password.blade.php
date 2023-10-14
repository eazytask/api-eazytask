@component('mail::message')
# Dear {{ ucwords($name) }},

Congratulations!
Your account has been setup and you are ready to explore Eazytask features from
{{ strtoupper($company) }}.

@component('mail::button', ['url' => 'https://eazytask.au'])
Login Now
@endcomponent

@component('mail::panel')
<center>
<a href="https://apps.apple.com/id/app/eazytask/id1642332032">
<img src="https://upload.wikimedia.org/wikipedia/commons/thumb/3/3c/Download_on_the_App_Store_Badge.svg/800px-Download_on_the_App_Store_Badge.svg.png"
alt="Download on the App Store" style="width: 200px; height: auto;">
</a>
<br>
<a href="https://play.google.com/store/apps/details?id=com.ni.Easytask">
<img src="https://play.google.com/intl/en_us/badges/static/images/badges/en_badge_web_generic.png"
alt="Get it on Google Play" style="width: 200px; height: auto;">
</a>
</center>
@endcomponent

Thank you for using our application!
If you need any further assistance, please contact our support team

---
Thanks,
{{ config('app.name') }}
@endcomponent
