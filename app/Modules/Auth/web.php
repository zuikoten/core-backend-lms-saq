<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Controllers\AuthController;
use Modules\Auth\Controllers\StaffPasswordResetController;
use Modules\Auth\Controllers\UserController;
use Modules\Auth\Controllers\RoleController;
use Modules\Auth\Controllers\ProfileController;

Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('login', [AuthController::class, 'login'])
    ->middleware('throttle:login')
    ->name('login.attempt');
Route::post('logout', [AuthController::class, 'logout'])->name('logout');

// Login via OTP WhatsApp (alternatif dari email+password)
Route::post('login/otp/request', [AuthController::class, 'requestLoginOtp'])
    ->middleware('throttle:otp-request')
    ->name('login.otp.request');
Route::get('login/otp/verify', [AuthController::class, 'showLoginOtpVerifyForm'])
    ->name('login.otp.verify.form');
Route::post('login/otp/verify', [AuthController::class, 'loginWithOtp'])
    ->middleware('throttle:login')
    ->name('login.otp.verify');

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
Route::post('forgot-password/otp/verify', [StaffPasswordResetController::class, 'verifyOtp'])
    ->middleware('throttle:login')
    ->name('password.otp.verify');

Route::get('forgot-password/otp/new-password', [StaffPasswordResetController::class, 'showNewPasswordForm'])
    ->name('password.otp.new-password.form');
Route::post('forgot-password/otp/new-password', [StaffPasswordResetController::class, 'setNewPassword'])
    ->middleware('throttle:login')
    ->name('password.otp.new-password');

// permission:panel.access -> siapa pun boleh masuk panel selama di-assign
// permission ini (superadmin otomatis lolos lewat Gate::before bypass,
// lihat AuthModuleServiceProvider::registerGates()), bukan role tertentu.
Route::middleware(['auth:web', 'permission:panel.access', \Modules\Auth\Middleware\EnsureUserIsActive::class])->group(function () {
    Route::get('staff/dashboard', function () {
        return view('modules.auth.dashboard');
    })->name('staff.dashboard');

    Route::post('staff/logout', [AuthController::class, 'logout'])->name('staff.logout');

    // Profile management (self-service)
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::put('profile/email', [ProfileController::class, 'updateEmail'])
        ->middleware('throttle:sensitive-profile-update')
        ->name('profile.email.update');
    Route::put('profile/phone', [ProfileController::class, 'updatePhone'])
        ->middleware('throttle:sensitive-profile-update')
        ->name('profile.phone.update');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])
        ->middleware('throttle:sensitive-profile-update')
        ->name('profile.password.update');
});

Route::middleware(['auth:web', 'permission:user.manage', \Modules\Auth\Middleware\EnsureUserIsActive::class])
    ->prefix('users')
    ->name('users.')
    ->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('{user}', [UserController::class, 'update'])->name('update');
        Route::delete('{user}', [UserController::class, 'destroy'])->name('destroy');
    });

Route::middleware(['auth:web', 'permission:role.manage', \Modules\Auth\Middleware\EnsureUserIsActive::class])
    ->prefix('roles')
    ->name('roles.')
    ->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('index');
        Route::get('create', [RoleController::class, 'create'])->name('create');
        Route::post('/', [RoleController::class, 'store'])->name('store');
        Route::get('{role}/edit', [RoleController::class, 'edit'])->name('edit');
        Route::put('{role}', [RoleController::class, 'update'])->name('update');
        Route::delete('{role}', [RoleController::class, 'destroy'])->name('destroy');
    });
