<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Middleware\EnsureUserIsActive;
use Modules\Finance\Controllers\InvoiceApiController;

Route::middleware(['auth:sanctum', EnsureUserIsActive::class])
    ->prefix('finance/invoices')
    ->group(function () {
        // 'summary' WAJIB didaftarkan sebelum '{invoice}' — kalau kebalik,
        // 'summary' ketangkep sebagai parameter {invoice} dan gagal di
        // route model binding.
        Route::get('summary', [InvoiceApiController::class, 'summary']);
        Route::get('/', [InvoiceApiController::class, 'index']);
        Route::get('{invoice}', [InvoiceApiController::class, 'show']);
    });