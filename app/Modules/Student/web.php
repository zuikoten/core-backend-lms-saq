<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Middleware\EnsureUserIsActive;
use Modules\Student\Controllers\StudentController;

// Permission 'students.manage' belum di-seed otomatis — sama seperti
// 'academic-years.manage', daftarkan manual lewat Tinker dulu.
Route::middleware(['auth:web', 'permission:student.manage', EnsureUserIsActive::class])
    ->prefix('students')
    ->name('students.')
    ->group(function () {
        Route::get('/', [StudentController::class, 'index'])->name('index');
        Route::get('create', [StudentController::class, 'create'])->name('create');
        Route::post('/', [StudentController::class, 'store'])->name('store');
        Route::get('parent-lookup', [StudentController::class, 'parentLookup'])->name('parent-lookup');
        Route::get('{student}/edit', [StudentController::class, 'edit'])->name('edit');
        Route::put('{student}', [StudentController::class, 'update'])->name('update');
        Route::put('{student}/parent', [StudentController::class, 'updateParent'])->name('parent.update');
        Route::delete('{student}', [StudentController::class, 'destroy'])->name('destroy');
    });