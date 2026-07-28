<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Controllers\AuthController;
use Modules\Auth\Controllers\StaffPasswordResetController;

Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('login', [AuthController::class, 'login'])
    ->middleware('throttle:login')
    ->name('login.attempt');
Route::post('logout', [AuthController::class, 'logout'])->name('logout');

// Halaman pilihan: reset via email atau via OTP WhatsApp
Route::get('forgot-password', [StaffPasswordResetController::class, 'showChooseForm'])
    ->name('password.request');

// Jalur 1: broker email bawaan Laravel
Route::get('forgot-password/email', [StaffPasswordResetController::class, 'showLinkRequestForm'])
    ->name('password.request.email');
Route::post('forgot-password/email', [StaffPasswordResetController::class, 'sendResetLinkEmail'])
    ->middleware('throttle:login')
    ->name('password.email');
Route::get('reset-password/{token}', [StaffPasswordResetController::class, 'showResetForm'])
    ->name('password.reset');
Route::post('reset-password', [StaffPasswordResetController::class, 'reset'])
    ->name('password.update');

// Jalur 2: OTP WhatsApp
Route::get('forgot-password/otp', [StaffPasswordResetController::class, 'showOtpRequestForm'])
    ->name('password.request.otp');
Route::post('forgot-password/otp', [StaffPasswordResetController::class, 'requestOtp'])
    ->middleware('throttle:otp-request')
    ->name('password.otp.request');
Route::get('forgot-password/otp/verify', [StaffPasswordResetController::class, 'showOtpVerifyForm'])
    ->name('password.otp.verify.form');
Route::post('forgot-password/otp/verify', [StaffPasswordResetController::class, 'resetWithOtp'])
    ->middleware('throttle:login')
    ->name('password.otp.verify');

// permission:panel.access -> siapa pun boleh masuk panel selama di-assign
// permission ini (superadmin otomatis lolos lewat Gate::before bypass,
// lihat AuthModuleServiceProvider::registerGates()), bukan role tertentu.
Route::middleware(['auth:web', 'permission:panel.access', \Modules\Auth\Middleware\EnsureUserIsActive::class])->group(function () {
    Route::get('staff/dashboard', function () {
        return view('modules.auth.dashboard');
    })->name('staff.dashboard');

    Route::post('staff/logout', [AuthController::class, 'logout'])->name('staff.logout');
});
