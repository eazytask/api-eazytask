<?php

use App\Http\Controllers\admin\MessagesController;
use App\Http\Controllers\admin\ActivityLogController;
use App\Http\Controllers\admin\ClientController;
use App\Http\Controllers\admin\EmployeeController;
use App\Http\Controllers\admin\EventCalendarController;
use App\Http\Controllers\admin\InductedController;
use App\Http\Controllers\admin\JobTypeController;
use App\Http\Controllers\admin\KioskController;
use App\Http\Controllers\admin\LeaveDayController;
use App\Http\Controllers\admin\PaymentController;
use App\Http\Controllers\admin\ProjectController;
use App\Http\Controllers\admin\RevenueController;
use App\Http\Controllers\admin\RoasterStatusController;
use App\Http\Controllers\admin\ScheduledCalendarController;
use App\Http\Controllers\admin\TimekeeperController;
use App\Http\Controllers\admin\UnavailabilityController;
use App\Http\Controllers\admin\ViewScheduleController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BasicController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SwitchCompanyController;
use App\Http\Controllers\user\SignInController;
use App\Http\Controllers\user\TimesheetController;
use App\Http\Controllers\user\UpcomingEventController;
use App\Http\Controllers\user\UserCalendarController;
use App\Http\Controllers\user\UserComplianceController;
use App\Http\Controllers\user\UserLeaveDayController;
use App\Http\Controllers\user\UserReportController;
use App\Http\Controllers\user\UserScheduledCalendar;
use App\Http\Controllers\user\UserShiftController;
use App\Http\Controllers\user\UserSummeryController;
use App\Http\Controllers\user\UserUnavailabilityCntroller;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\TestEmail;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Route::get('/test-email-for-development', function () {
//     // Replace 'youremail@example.com' with your recipient's email address
//     $recipientEmail = 'mawanher07@gmail.com';

//     // Send a test email
//     try {
//         Mail::to($recipientEmail)->send(new TestEmail());
//         Log::info('Test email sent successfully.');
//     } catch (\Exception $e) {
//         Log::error('Failed to send test email: ' . $e->getMessage());
//         return "Failed to send test email: " . $e->getMessage();
//     }

//     return "Test email sent successfully!";
// });

Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/admin/login', [AuthController::class, 'admin_login']);
    Route::post('/forget/password', [AuthController::class, 'forget_password']);
    Route::post('/reset/password', [AuthController::class, 'reset_password']);
    Route::get('/handler-reset', function () {
        return redirect('https://eazytask.au/password/reset/'.request()->get('token').'?email='.request()->get('email'));
    })->name('password.reset');

    Route::middleware(['auth:api'])->group(function () {
        # authentication routes
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/update/firebase/token', [AuthController::class, 'update_firebase_token']);

        # switch company and role
        Route::get('companies', [SwitchCompanyController::class, 'index']);
        Route::get('admin/companies', [SwitchCompanyController::class, 'admin_companies']);
        Route::get('companies/current', [SwitchCompanyController::class, 'current_company']);
        Route::get('company/switch/{company}', [SwitchCompanyController::class, 'switch']);
        // Route::get('company/switch/{company}', [SwitchCompanyController::class, 'switch']);
        Route::get('role/switch/{change_role}', [SwitchCompanyController::class, 'switch_role']);

        # profile routes
        Route::get('/notifications', [ProfileController::class, 'notifications']);
        Route::get('/notifications/delete', [ProfileController::class, 'delete_notifications']);
        Route::get('/notifications/mark/as/read', [ProfileController::class, 'read_notifications']);
        Route::get('profile', [ProfileController::class, 'profile']);
        Route::post('profile/update', [ProfileController::class, 'profile_update']);
        Route::post('change/password', [ProfileController::class, 'change_password']);
        Route::post('image/update', [ProfileController::class, 'image_update']);

        # basic/necessary
        Route::get('/leave/type', [BasicController::class, 'leave_type']);
        Route::get('/job/type', [BasicController::class, 'job_type']);
        Route::get('/roster/status', [BasicController::class, 'roster_status']);
        Route::get('/projects/{status?}', [BasicController::class, 'projects']);
        Route::get('/employees/{status?}', [BasicController::class, 'employees']);
        Route::get('/compliances', [BasicController::class, 'compliances']);

        Route::get('messages', [MessagesController::class, 'index']);
        Route::post('messages', [MessagesController::class, 'store'])->middleware('is_admin');
        Route::put('messages', [MessagesController::class, 'update']);
        Route::post('messages/reply', [MessagesController::class, 'storeReply']);
        Route::post('messages/confirm', [MessagesController::class, 'confirm']);
        Route::post('messages/unconfirm', [MessagesController::class, 'unconfirm']);
        Route::post('messages/destroy', [MessagesController::class, 'destroy']);
        Route::post('messages/update-reply', [MessagesController::class, 'updateReply']);
        Route::post('messages/destroy-reply', [MessagesController::class, 'destroyReply']);
        
        # all admin routes
        Route::prefix('admin')->group(function () {
            //for user and admin (new update ui)
            Route::get('employee', [EmployeeController::class, 'index']);
            
            #my availability
            Route::get('employee/unavailability', [UnavailabilityController::class, 'index']);
            Route::get('employee/unavailability/total', [UnavailabilityController::class, 'index_total']);
            Route::post('employee/unavailability', [UnavailabilityController::class, 'store']);
            Route::put('employee/unavailability', [UnavailabilityController::class, 'update']);
            Route::delete('employee/unavailability/{id}', [UnavailabilityController::class, 'destroy']);
            Route::get('employee/shift-details', [EmployeeController::class, 'employee_shift_details']);
            Route::get('employee/shift-details/{employee_id}', [EmployeeController::class, 'employee_shift_details_by_id']);

            Route::middleware(['is_admin'])->group(function () {

                #event
                Route::post('event', [EventCalendarController::class, 'store']);
                Route::put('event', [EventCalendarController::class, 'update']);
                Route::delete('event/{id}', [EventCalendarController::class, 'delete']);

                #event calendar
                Route::get('event/calendar/data', [EventCalendarController::class, 'index']);
                Route::get('event/employees/{event_id}', [EventCalendarController::class, 'get_employees']);
                Route::post('event/employees/publish', [EventCalendarController::class, 'publish']);

                #view schedule
                Route::get('view/schedule/data', [ViewScheduleController::class, 'index']);
                Route::get('view/schedule/rosters', [ViewScheduleController::class, 'get_rosters']);
                Route::get('roster/approve/{ids}', [ViewScheduleController::class, 'approve']);
                Route::post('view/schedule/roster/update', [ViewScheduleController::class, 'update']);

                #scheduled calendar
                Route::get('sign/in/status/data', [ScheduledCalendarController::class, 'sign_in_status']);
                Route::get('roster/approve', [ScheduledCalendarController::class, 'approve_week']);
                Route::get('roster/calendar/data', [ScheduledCalendarController::class, 'get_roster_enrty']);

                Route::get('scheduled/calendar/data', [ScheduledCalendarController::class, 'index']);
                Route::get('scheduled/calendar/filter/employee', [ScheduledCalendarController::class, 'filter_emoployee']);
                Route::get('scheduled/calendar/week/publish', [ScheduledCalendarController::class, 'publish']);
                Route::get('scheduled/calendar/week/copy', [ScheduledCalendarController::class, 'copy_week']);
                Route::get('scheduled/calendar/drop/roster', [ScheduledCalendarController::class, 'drop_roster']);

                #activity log
                Route::get('activity/log', [ActivityLogController::class, 'index']);
                Route::post('activity/log/search', [ActivityLogController::class, 'search']);

                #admin new roster-timekeeper
                Route::get('timekeeper', [TimekeeperController::class, 'index']);
                Route::post('timekeeper', [TimeKeeperController::class, 'store']);
                Route::put('timekeeper', [TimeKeeperController::class, 'update']);
                Route::delete('timekeeper/{id}', [TimeKeeperController::class, 'delete']);
                // Route::post('timekeeper/search', [TimekeeperController::class, 'search']);

                #employees
                Route::post('employee', [EmployeeController::class, 'store']);
                Route::put('employee', [EmployeeController::class, 'update']);
                Route::delete('employee/{id}', [EmployeeController::class, 'delete']);
                Route::post('employee/compliance', [EmployeeController::class, 'filter_compliance']);
                // Route::get('/compliance', [EmployeeController::class, 'compliances']);

                #my leave day
                Route::get('employee/leave/day', [LeaveDayController::class, 'index']);
                Route::post('employee/leave/day', [LeaveDayController::class, 'store']);
                Route::put('employee/leave/day', [LeaveDayController::class, 'update']);
                Route::delete('employee/leave/day/{id}', [LeaveDayController::class, 'destroy']);

                # Induction routes
                Route::get('inducted/site', [InductedController::class, 'index']);
                Route::post('inducted/site', [InductedController::class, 'store']);
                Route::put('inducted/site', [InductedController::class, 'update']);
                Route::delete('inducted/site/{id}', [InductedController::class, 'delete']);

                #clients
                Route::get('client', [ClientController::class, 'index']);
                Route::post('client', [ClientController::class, 'store']);
                Route::put('client', [ClientController::class, 'update']);
                Route::delete('client/{id}', [ClientController::class, 'delete']);

                #projects
                Route::get('project', [ProjectController::class, 'index']);
                Route::post('project', [ProjectController::class, 'store']);
                Route::put('project', [ProjectController::class, 'update']);
                Route::delete('project/{id}', [ProjectController::class, 'delete']);

                #Revenue routes
                Route::get('revenue', [RevenueController::class, 'index']);
                Route::post('revenue', [RevenueController::class, 'store']);
                Route::put('revenue', [RevenueController::class, 'update']);
                Route::delete('revenue/{id}', [RevenueController::class, 'delete']);
                Route::post('revenue/search', [RevenueController::class, 'search']);

                #payment
                Route::get('payment/data', [PaymentController::class, 'index']);
                Route::get('payment/rosters', [PaymentController::class, 'get_rosters']);
                // Route::post('payment/rosters/update', [PaymentController::class, 'update_rosters']);
                Route::post('payment/add', [PaymentController::class, 'add_payment']);

                #job type
                Route::get('job/type', [JobTypeController::class, 'index']);
                Route::post('job/type', [JobTypeController::class, 'store']);
                Route::put('job/type', [JobTypeController::class, 'update']);
                Route::delete('job/type/{id}', [JobTypeController::class, 'destroy']);

                #roster status
                Route::get('roster/status', [RoasterStatusController::class, 'index']);
                Route::post('roster/status', [RoasterStatusController::class, 'store']);
                Route::put('roster/status', [RoasterStatusController::class, 'update']);
                Route::delete('roster/status/{id}', [RoasterStatusController::class, 'destroy']);

                #kiosk
                Route::get('kiosk/employees', [KioskController::class, 'fetch_employees']);
                Route::get('kiosk/employee/shift', [KioskController::class, 'employee_shift']);
                Route::post('kiosk/check/pin', [KioskController::class, 'check_pin']);
                Route::post('kiosk/sign/in', [KioskController::class, 'signIn']);
                Route::post('kiosk/sign/out', [KioskController::class, 'signOut']);
                Route::post('kiosk/start/unschedule', [KioskController::class, 'storeTimekeeper']);
            });
        });

        # all employee routes
        Route::prefix('user')->group(function () {
            Route::middleware(['is_user'])->group(function () {
                #all featur summery like total notification
                Route::get('summery', [UserSummeryController::class, 'index']);
                #change pin
                Route::post('change/pin', [ProfileController::class, 'change_pin']);

                #user calendar
                Route::get('scheduled/calendar/data', [UserScheduledCalendar::class, 'index']);
                Route::get('calendar/data', [UserCalendarController::class, 'index']);
                // Route::get('calendar/data/{id}', [UserCalendarController::class, 'show']);

                #compliance
                Route::get('compliance', [UserComplianceController::class, 'index']);
                Route::post('compliance', [UserComplianceController::class, 'store']);
                Route::put('compliance', [UserComplianceController::class, 'store']);
                Route::delete('compliance/{id}', [UserComplianceController::class, 'distroy']);

                #timesheet
                Route::get('timesheet', [TimesheetController::class, 'index']);
                Route::post('timesheet', [TimesheetController::class, 'store']);
                Route::put('timesheet', [TimesheetController::class, 'update']);
                Route::delete('timesheet/{id}', [TimesheetController::class, 'delete']);
                // Route::get('timesheet/search', [TimesheetController::class, 'search']);

                #sign in timekeeper
                Route::get('sign/in', [SignInController::class, 'index']);
                Route::post('sign/in/timekeeper', [SignInController::class, 'signIn']);
                Route::post('sign/out/timekeeper', [SignInController::class, 'signOut']);
                Route::post('start/unschedule', [SignInController::class, 'storeTimekeeper']);

                #shift routes
                Route::get('unconfirm/shift', [UserShiftController::class, 'unconfirmed_shift']);
                Route::post('unconfirm/shift', [UserShiftController::class, 'confirm_shift']);
                Route::get('upcoming/shift', [UserShiftController::class, 'upcoming_shift']);
                Route::get('past/shift', [UserShiftController::class, 'past_shift']);

                #upcoming event
                Route::get('upcoming/event', [UpcomingEventController::class, 'index']);
                Route::post('upcoming/event/confirm', [UpcomingeventController::class, 'event_confirm']);

                #roster report
                Route::get('roster/report', [UserReportController::class, 'roster_report']);
                // Route::post('roster/report', [UserReportController::class, 'search_roster_report']);

                #payment report
                Route::get('payment/report', [UserReportController::class, 'payment_reports']);
                // // Route::get('payment/invoice/{id}', [UserReportController::class, 'view_payment_invoice']);
                // Route::get('payment/invoice/download/{id}', [UserReportController::class, 'download_payment_invoice']);

                #my unavailability
                Route::get('unavailability', [UserUnavailabilityCntroller::class, 'index']);
                Route::post('unavailability', [UserUnavailabilityCntroller::class, 'store']);
                Route::put('unavailability', [UserUnavailabilityCntroller::class, 'update']);
                Route::delete('unavailability/{id}', [UserUnavailabilityCntroller::class, 'destroy']);

                #leave day
                Route::get('leave/day', [UserLeaveDayController::class, 'index']);
                Route::post('leave/day', [UserLeaveDayController::class, 'store']);
                Route::put('leave/day', [UserLeaveDayController::class, 'update']);
                Route::delete('leave/day/{id}', [UserLeaveDayController::class, 'destroy']);
            });
        });
    });

    Route::get('payment/invoice/download/{id}', [UserReportController::class, 'download_payment_invoice']);
    Route::get('roster/report/download', [UserReportController::class, 'download_roster_report']);
});
