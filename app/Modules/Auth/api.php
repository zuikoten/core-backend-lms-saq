<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Controllers\ParentAuthController;
use Modules\Auth\Middleware\EnsureUserIsActive;

Route::prefix('auth')->group(function () {
    Route::post('otp/request', [ParentAuthController::class, 'requestOtp'])
        ->middleware('throttle:otp-request');

    Route::post('otp/verify', [ParentAuthController::class, 'verifyOtp'])
        ->middleware('throttle:otp-request');

    Route::post('login', [ParentAuthController::class, 'login'])
        ->middleware('throttle:login');

    Route::post('logout', [ParentAuthController::class, 'logout'])
        ->middleware(['auth:sanctum', EnsureUserIsActive::class]);
});
