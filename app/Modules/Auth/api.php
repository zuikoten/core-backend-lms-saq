<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Controllers\ParentAuthController;
use Modules\Auth\Middleware\EnsureUserIsActive;
use Modules\Auth\Controllers\ParentProfileApiController;

Route::prefix('auth')->group(function () {
    Route::post('otp/request', [ParentAuthController::class, 'requestOtp'])
        ->middleware('throttle:otp-request');

    Route::post('otp/verify', [ParentAuthController::class, 'verifyOtp'])
        ->middleware('throttle:otp-request');

    Route::post('login', [ParentAuthController::class, 'login'])
        ->middleware('throttle:login');

    Route::post('logout', [ParentAuthController::class, 'logout'])
        ->middleware(['auth:sanctum', EnsureUserIsActive::class]);

    Route::post('credentials', [ParentAuthController::class, 'setCredentials'])
        ->middleware(['auth:sanctum', EnsureUserIsActive::class]);
});

Route::middleware(['auth:sanctum', EnsureUserIsActive::class])
    ->prefix('profile')
    ->group(function () {
        Route::get('/', [ParentProfileApiController::class, 'show']);
        Route::put('/', [ParentProfileApiController::class, 'update']);

        Route::put('email', [ParentProfileApiController::class, 'updateEmail'])
            ->middleware('throttle:sensitive-profile-update');
        Route::put('password', [ParentProfileApiController::class, 'updatePassword'])
            ->middleware('throttle:sensitive-profile-update');

        Route::post('phone/otp/request', [ParentProfileApiController::class, 'requestPhoneChangeOtp'])
            ->middleware('throttle:otp-request');
        Route::post('phone/otp/confirm', [ParentProfileApiController::class, 'confirmPhoneChange'])
            ->middleware('throttle:login');
    });
