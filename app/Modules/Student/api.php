<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Middleware\EnsureUserIsActive;
use Modules\Student\Controllers\StudentApiController;

Route::middleware(['auth:sanctum', EnsureUserIsActive::class])
    ->prefix('students')
    ->group(function () {
        Route::get('/', [StudentApiController::class, 'index']);
        Route::get('/parent', [StudentApiController::class, 'parent']);
    });
