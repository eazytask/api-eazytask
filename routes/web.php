<?php

use App\Http\Controllers\user\UserReportController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('clear-cache', function () {
    Artisan::call('cache:clear');

    Artisan::call('config:cache');
    Artisan::call('config:clear');

    // Artisan::call('route:cache');
    Artisan::call('route:clear');

    Artisan::call('view:cache');
    Artisan::call('view:clear');

    // Artisan::call('passport:install');
    // return Artisan::call('queue:restart');

    echo 'all cache cleared';
});

// Route::get('pdf/{id}', [UserReportController::class, 'download_payment_invoice']);