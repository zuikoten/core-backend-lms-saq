<?php

use Illuminate\Support\Facades\Route;
use Modules\Academic\Controllers\ClassGroupApiController;
use Modules\Academic\Controllers\ReportCardApiController;
use Modules\Auth\Middleware\EnsureUserIsActive;

Route::middleware(['auth:sanctum', EnsureUserIsActive::class])->group(function () {
    Route::get('class-groups', [ClassGroupApiController::class, 'index']);
    Route::get('report-cards', [ReportCardApiController::class, 'index']);
});
