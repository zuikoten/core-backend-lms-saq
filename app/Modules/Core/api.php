<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Middleware\EnsureUserIsActive;
use Modules\Core\Controllers\AcademicYearApiController;

Route::middleware(['auth:sanctum', EnsureUserIsActive::class])
    ->prefix('academic-years')
    ->group(function () {
        Route::get('/', [AcademicYearApiController::class, 'index']);
        Route::get('active', [AcademicYearApiController::class, 'active']);
    });
