@component('mail::message')
    # Dear {{ ucwords($name) }},

    Congratulations!
    Your account has been setup and you are ready to explore Eazytask features from
    {{ strtoupper($company) }}.

    Please don't share your credentials with anyone and after login change your password. Your
    account temporary login credential below-

    User Email: {{ $email }}
    User Password: {{ $user_password }}

    <a href="https://eazytask.au" class="m_-696983200501294590button" rel="noopener"
        style="box-sizing:border-box;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif,'Apple Color Emoji','Segoe UI Emoji','Segoe UI Symbol';border-radius:4px;color:#fff;display:inline-block;overflow:hidden;text-decoration:none;background-color:#2d3748;border-bottom:8px solid #2d3748;border-left:18px solid #2d3748;border-right:18px solid #2d3748;border-top:8px solid #2d3748"
        target="_blank"
        data-saferedirecturl="https://www.google.com/url?q=https://eazytask.au&amp;source=gmail&amp;ust=1686933787476000&amp;usg=AOvVaw04_poale2ij8x8nn4pe6MO">Login
        Now</a>

    Thank you for using our application!
    If you need any further assistance, please contact our support team

    ---
    Thanks,<br>
    {{ config('app.name') }}
@endcomponent
