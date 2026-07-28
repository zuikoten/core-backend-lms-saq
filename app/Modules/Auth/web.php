<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Controllers\AdminPasswordResetController;
use Modules\Auth\Controllers\AuthController;

Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('login', [AuthController::class, 'login'])
    ->middleware('throttle:login')
    ->name('login.attempt');
Route::post('logout', [AuthController::class, 'logout'])->name('logout');

Route::get('forgot-password', [AdminPasswordResetController::class, 'showLinkRequestForm'])
    ->name('password.request');
Route::post('forgot-password', [AdminPasswordResetController::class, 'sendResetLinkEmail'])
    ->middleware('throttle:login')
    ->name('password.email');
Route::get('reset-password/{token}', [AdminPasswordResetController::class, 'showResetForm'])
    ->name('password.reset');
Route::post('reset-password', [AdminPasswordResetController::class, 'reset'])
    ->name('password.update');

Route::middleware(['auth:web', 'role:superadmin', \Modules\Auth\Middleware\EnsureUserIsActive::class])->group(function () {
    Route::get('admin/dashboard', function () {
        return view('modules.auth.dashboard');
    })->name('admin.dashboard');

    Route::post('admin/logout', [AuthController::class, 'logout'])->name('admin.logout');
});
