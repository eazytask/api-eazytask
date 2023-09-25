@component('mail::message')
    @component('mail::panel')
        <div class="content">
            <td align="left">
                <table border="0" width="80%" align="center" cellpadding="0" cellspacing="0" class="container590">
                    <tr>
                        <td align="left" style="color: #888888; width:20px; font-size: 16px; line-height: 24px;">
                            <!-- section text ======-->
                            {{--                    <h4 style="text-align: center;"> <span style="border-bottom: 1px solid dimgray;">{{ app_name() }}</span></h4> --}}

                            <p style="line-height: 24px; margin-bottom:15px;">
                                <strong>Dear {{ $name }},</strong> <br />
                                Congratulations!
                                .
                            </p>
                            <p style="line-height: 24px; margin-bottom:15px;">
                                Your account has been setup and you are ready to explore Eazytask features from
                                {{ strtoupper($company) }}.
                            </p>

                            <p style="line-height: 24px; margin-bottom:20px;">
                                Please don't share your credentials with anyone and after login change your password. Your
                                account temporary login credential below-
                            </p>
                            <div
                                style="text-align: center; color: black; font-weight: bold; border: 1px solid black; padding: 15px ">

                                <p>User Email: {{ $email }}</p>
                                <p>User Password: {{ $user_password }}</p>
                            </div>


                            <table align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation"
                                style="box-sizing:border-box;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif,'Apple Color Emoji','Segoe UI Emoji','Segoe UI Symbol';margin:15px auto;padding:0;text-align:center;width:100%">
                                <tbody>
                                    <tr>
                                        <td align="center"
                                            style="box-sizing:border-box;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif,'Apple Color Emoji','Segoe UI Emoji','Segoe UI Symbol'">
                                            <table width="100%" border="0" cellpadding="0" cellspacing="0"
                                                role="presentation"
                                                style="box-sizing:border-box;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif,'Apple Color Emoji','Segoe UI Emoji','Segoe UI Symbol'">
                                                <tbody>
                                                    <tr>
                                                        <td align="center"
                                                            style="box-sizing:border-box;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif,'Apple Color Emoji','Segoe UI Emoji','Segoe UI Symbol'">
                                                            <table border="0" cellpadding="0" cellspacing="0"
                                                                role="presentation"
                                                                style="box-sizing:border-box;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif,'Apple Color Emoji','Segoe UI Emoji','Segoe UI Symbol'">
                                                                <tbody>
                                                                    <tr>
                                                                        <td
                                                                            style="text-align:center;box-sizing:border-box;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif,'Apple Color Emoji','Segoe UI Emoji','Segoe UI Symbol'">
                                                                            <a href="https://eazytask.au"
                                                                                class="m_-696983200501294590button"
                                                                                rel="noopener"
                                                                                style="box-sizing:border-box;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif,'Apple Color Emoji','Segoe UI Emoji','Segoe UI Symbol';border-radius:4px;color:#fff;display:inline-block;overflow:hidden;text-decoration:none;background-color:#2d3748;border-bottom:8px solid #2d3748;border-left:18px solid #2d3748;border-right:18px solid #2d3748;border-top:8px solid #2d3748"
                                                                                target="_blank"
                                                                                data-saferedirecturl="https://www.google.com/url?q=https://eazytask.au&amp;source=gmail&amp;ust=1686933787476000&amp;usg=AOvVaw04_poale2ij8x8nn4pe6MO">Login
                                                                                Now</a>
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <p style="line-height: 24px; margin-bottom:20px;margin-top:20px">
                                Thank you for using our application!
                                If you need any further assistance, please contact our support team
                            </p>

                            <p style="line-height: 24px">
                                Regards, <br />
                                www.eazytask.au

                                {{-- @yield('title', app_name()) --}}
                            </p>

                            <br />

                            <p class="small" style="line-height: 24px; margin-bottom:20px;">
                                If you’re having trouble to access your account , please communicate with Admin.
                            </p>

                            {{-- <p class="small" style="line-height: 24px; margin-bottom:20px;">
                        <a href="{{ $user_password }}" target="_blank" class="lap">
                            {{ $user_password}}
                        </a>
                    </p> --}}

                        </td>
                    </tr>
                </table>
            </td>
        </div>
    @endcomponent
@endcomponent
