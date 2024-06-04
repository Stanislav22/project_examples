<?php

use App\Calendars\Http\Controllers\GoogleCalendarController;
use App\Calendars\Http\Controllers\OutlookCalendarController;
use App\Customers\Http\Controllers\CustomerController;
use App\Http\Controllers\ApplicationFunctionController;
use App\Http\Controllers\ZipController;
use App\Printers\Http\Controllers\PrinterController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TokenController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SocialLoginController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\LeadTimeController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\WebhookController;

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

Route::middleware(['auth:sanctum', 'customerStatus'])
    ->group(function () {
        Route::get('/user', [UserController::class, 'user']);
        Route::get('/logout', [UserController::class, 'logout']);
        Route::post('/change-password', [UserController::class, 'changePassword']);
    });

Route::get('/check-email/{email}', [UserController::class, 'checkEmail'])->where('email', '.*');
Route::post('/login', [UserController::class, 'login']);
Route::post('/forgot-password', [UserController::class, 'forgotPassword']);
Route::post('/reset-password', [UserController::class, 'resetPassword']);
