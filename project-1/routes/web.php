<?php

use App\Calendars\Http\Controllers\GoogleCalendarController;
use App\Calendars\Http\Controllers\OutlookCalendarController;
use App\Http\Controllers\QrCodesController;
use App\Http\Middleware\CheckXeroWebhookSignature;
use App\Xero\Http\Controllers\AuthorizationCallbackController;
use App\Xero\Http\Controllers\XeroWebhookController;
use Illuminate\Support\Facades\Route;
use App\Payments\Http\Controllers\StripeConnectController;
use App\Http\Controllers\SocialLoginController;

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

Route::prefix('social-login')
    ->group(function() {
        Route::get('/facebook/redirect', [SocialLoginController::class, 'facebookRedirect']);
        Route::get('/google/redirect', [SocialLoginController::class, 'googleRedirect']);
    });
