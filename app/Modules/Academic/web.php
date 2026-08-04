<?php

use Illuminate\Support\Facades\Route;
use Modules\Academic\Controllers\ClassGroupController;
use Modules\Academic\Controllers\ClassGroupStudentController;
use Modules\Academic\Controllers\ReportCardController;
use Modules\Auth\Middleware\EnsureUserIsActive;

// Permission 'academic.manage' — daftarkan manual lewat seeder/Tinker.
// Rencana ke depan: input nilai per mapel akan pakai permission terpisah
// level guru/wali kelas, begitu modul Teacher digarap — lihat README.
Route::middleware(['auth:web', 'permission:academic.manage', EnsureUserIsActive::class])->group(function () {

    Route::prefix('class-groups')->name('class-groups.')->group(function () {
        Route::get('/', [ClassGroupController::class, 'index'])->name('index');
        Route::get('create', [ClassGroupController::class, 'create'])->name('create');
        Route::post('/', [ClassGroupController::class, 'store'])->name('store');
        Route::get('{classGroup}', [ClassGroupController::class, 'show'])->name('show');
        Route::get('{classGroup}/edit', [ClassGroupController::class, 'edit'])->name('edit');
        Route::put('{classGroup}', [ClassGroupController::class, 'update'])->name('update');
        Route::delete('{classGroup}', [ClassGroupController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('class-group-students')->name('class-group-students.')->group(function () {
        Route::get('/', [ClassGroupStudentController::class, 'index'])->name('index');
        Route::post('/', [ClassGroupStudentController::class, 'store'])->name('store');
        Route::post('{classGroupStudent}/transfer', [ClassGroupStudentController::class, 'transfer'])->name('transfer');
    });

    Route::prefix('report-cards')->name('report-cards.')->group(function () {
        Route::get('/', [ReportCardController::class, 'index'])->name('index');
        Route::get('create', [ReportCardController::class, 'create'])->name('create');
        Route::post('/', [ReportCardController::class, 'store'])->name('store');
        Route::get('{reportCard}/edit', [ReportCardController::class, 'edit'])->name('edit');
        Route::put('{reportCard}', [ReportCardController::class, 'update'])->name('update');
        Route::post('{reportCard}/publish', [ReportCardController::class, 'publish'])->name('publish');
        Route::delete('{reportCard}', [ReportCardController::class, 'destroy'])->name('destroy');
    });
});
