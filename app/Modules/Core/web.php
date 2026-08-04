<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Middleware\EnsureUserIsActive;
use Modules\Core\Controllers\AcademicYearController;
use Modules\Core\Controllers\GradeLevelController;
use Modules\Core\Controllers\JenjangController;
use Modules\Core\Controllers\SemesterController;

// Permission 'core.manage' — daftarkan manual lewat seeder/Tinker sebelum
// modul ini dipakai (lihat README.md modul Core).
Route::middleware(['auth:web', 'permission:core.manage', EnsureUserIsActive::class])->group(function () {

    Route::prefix('academic-years')->name('academic-years.')->group(function () {
        Route::get('/', [AcademicYearController::class, 'index'])->name('index');
        Route::get('create', [AcademicYearController::class, 'create'])->name('create');
        Route::post('/', [AcademicYearController::class, 'store'])->name('store');
        Route::get('{academicYear}/edit', [AcademicYearController::class, 'edit'])->name('edit');
        Route::put('{academicYear}', [AcademicYearController::class, 'update'])->name('update');
        Route::post('{academicYear}/activate', [AcademicYearController::class, 'activate'])->name('activate');
        Route::delete('{academicYear}', [AcademicYearController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('jenjang')->name('jenjang.')->group(function () {
        Route::get('/', [JenjangController::class, 'index'])->name('index');
        Route::get('create', [JenjangController::class, 'create'])->name('create');
        Route::post('/', [JenjangController::class, 'store'])->name('store');
        Route::get('{jenjang}/edit', [JenjangController::class, 'edit'])->name('edit');
        Route::put('{jenjang}', [JenjangController::class, 'update'])->name('update');
        Route::delete('{jenjang}', [JenjangController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('grade-levels')->name('grade-levels.')->group(function () {
        Route::get('/', [GradeLevelController::class, 'index'])->name('index');
        Route::get('create', [GradeLevelController::class, 'create'])->name('create');
        Route::post('/', [GradeLevelController::class, 'store'])->name('store');
        Route::get('{gradeLevel}/edit', [GradeLevelController::class, 'edit'])->name('edit');
        Route::put('{gradeLevel}', [GradeLevelController::class, 'update'])->name('update');
        Route::delete('{gradeLevel}', [GradeLevelController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('semesters')->name('semesters.')->group(function () {
        Route::get('/', [SemesterController::class, 'index'])->name('index');
        Route::get('create', [SemesterController::class, 'create'])->name('create');
        Route::post('/', [SemesterController::class, 'store'])->name('store');
        Route::get('{semester}/edit', [SemesterController::class, 'edit'])->name('edit');
        Route::put('{semester}', [SemesterController::class, 'update'])->name('update');
        Route::post('{semester}/activate', [SemesterController::class, 'activate'])->name('activate');
        Route::delete('{semester}', [SemesterController::class, 'destroy'])->name('destroy');
    });
});
