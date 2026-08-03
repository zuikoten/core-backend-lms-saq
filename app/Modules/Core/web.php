<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Middleware\EnsureUserIsActive;
use Modules\Core\Controllers\AcademicYearController;

// Permission 'academic-years.manage' BELUM di-seed otomatis — daftarkan
// manual lewat Tinker/seeder dulu sebelum modul ini dipakai, konsisten
// dengan catatan modul Auth ("granular permission menyusul begitu modul
// terkait mulai digarap, di-seed oleh modul masing-masing").
Route::middleware(['auth:web', 'permission:core.manage', EnsureUserIsActive::class])
    ->prefix('academic-years')
    ->name('academic-years.')
    ->group(function () {
        Route::get('/', [AcademicYearController::class, 'index'])->name('index');
        Route::get('create', [AcademicYearController::class, 'create'])->name('create');
        Route::post('/', [AcademicYearController::class, 'store'])->name('store');
        Route::get('{academicYear}/edit', [AcademicYearController::class, 'edit'])->name('edit');
        Route::put('{academicYear}', [AcademicYearController::class, 'update'])->name('update');
        Route::post('{academicYear}/activate', [AcademicYearController::class, 'activate'])->name('activate');
        Route::delete('{academicYear}', [AcademicYearController::class, 'destroy'])->name('destroy');
    });
