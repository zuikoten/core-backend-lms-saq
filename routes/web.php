<?php

use Illuminate\Support\Facades\Route;
use App\Dashboard\Controllers\DashboardController;
use Modules\Auth\Middleware\EnsureUserIsActive;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth:web', 'permission:panel.access', EnsureUserIsActive::class])->group(function () {
    Route::get('staff/dashboard', [DashboardController::class, 'index'])->name('staff.dashboard');
});
