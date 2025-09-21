<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AppController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RideController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\SupportController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


// ====================================== AUTHENTICATION API'S ==============================================================
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/otp/send', [AuthController::class, 'sendOtp']);
    Route::post('/otp/verify', [AuthController::class, 'verifyOtp']);
});


Route::middleware('auth:sanctum')->get('/auth/check', [AuthController::class, 'checkAuth']);
Route::middleware('auth:sanctum')->post('/auth/logout', [AuthController::class, 'logout']);

// ====================================== APP / SPLASH ==============================================================
Route::get('/app/config', [AppController::class, 'config']);
Route::get('/content/terms', [AppController::class, 'terms']);
Route::get('/content/privacy', [AppController::class, 'privacy']);

// ====================================== USER ==============================================================
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user/me', [UserController::class, 'me']);
    Route::put('/user/me', [UserController::class, 'updateMe']);
    Route::post('/user/gender', [UserController::class, 'updateGender']);
    
    // Rides
    Route::post('/rides/quote', [RideController::class, 'quote']);
    Route::post('/rides/request', [RideController::class, 'requestRide']);
    Route::get('/rides', [RideController::class, 'list']);
    Route::get('/rides/{ride}', [RideController::class, 'show']);
    Route::post('/rides/{ride}/cancel', [RideController::class, 'cancel']);
    Route::post('/rides/{ride}/start', [RideController::class, 'start']); // driver only
    Route::post('/rides/{ride}/complete', [RideController::class, 'complete']); // driver only

    // Driver live
    Route::post('/driver/online', [DriverController::class, 'setOnline']);
    Route::post('/driver/location', [DriverController::class, 'updateLocation']);
    Route::get('/rides/{ride}/live', [DriverController::class, 'rideLive']);

    // Rating
    Route::post('/rides/{ride}/rate', [RatingController::class, 'rate']);

    // Support
    Route::post('/support/contact', [SupportController::class, 'contact']);
});