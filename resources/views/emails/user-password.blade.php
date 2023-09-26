@component('mail::message')
    # Hello {{ ucwords($name) }},

    ---
    Thanks,<br>
    {{ config('app.name') }}
@endcomponent
