<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Middleware\EnsureUserIsActive;
use Modules\Finance\Controllers\InvoiceApiController;
use Modules\Finance\Controllers\InvoiceCheckoutApiController;
use Modules\Finance\Controllers\XenditWebhookController;

Route::middleware(['auth:sanctum', EnsureUserIsActive::class])
    ->prefix('finance/invoices')
    ->group(function () {
        // 'summary' WAJIB didaftarkan sebelum '{invoice}' — kalau kebalik,
        // 'summary' ketangkep sebagai parameter {invoice} dan gagal di
        // route model binding.
        Route::get('summary', [InvoiceApiController::class, 'summary']);
        Route::get('/', [InvoiceApiController::class, 'index']);
        Route::get('{invoice}', [InvoiceApiController::class, 'show']);
        Route::post('{invoice}/checkout', [InvoiceCheckoutApiController::class, 'store'])
            ->middleware('throttle:6,1'); // maksimal 6 request/menit per user
    });
Route::post('webhooks/xendit/invoice', [XenditWebhookController::class, 'store']);
