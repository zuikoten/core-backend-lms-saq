<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Middleware\EnsureUserIsActive;
use Modules\Finance\Controllers\BillingTariffController;
use Modules\Finance\Controllers\BillingTypeController;
use Modules\Finance\Controllers\PaymentChannelController;
use Modules\Finance\Controllers\StudentTariffMappingController;
use Modules\Finance\Controllers\InvoiceController;
use Modules\Finance\Controllers\FinancialReportController;

Route::middleware(['auth:web', 'permission:finance.manage', EnsureUserIsActive::class])
    ->prefix('finance')
    ->name('finance.')
    ->group(function () {
        Route::prefix('billing-types')
            ->name('billing-types.')
            ->group(function () {
                Route::get('/', [BillingTypeController::class, 'index'])->name('index');
                Route::get('create', [BillingTypeController::class, 'create'])->name('create');
                Route::post('/', [BillingTypeController::class, 'store'])->name('store');
                Route::get('{billingType}/edit', [BillingTypeController::class, 'edit'])->name('edit');
                Route::put('{billingType}', [BillingTypeController::class, 'update'])->name('update');
                Route::delete('{billingType}', [BillingTypeController::class, 'destroy'])->name('destroy');
            });

        Route::prefix('payment-channels')
            ->name('payment-channels.')
            ->group(function () {
                Route::get('/', [PaymentChannelController::class, 'index'])->name('index');
                Route::get('create', [PaymentChannelController::class, 'create'])->name('create');
                Route::post('/', [PaymentChannelController::class, 'store'])->name('store');
                Route::get('{paymentChannel}/edit', [PaymentChannelController::class, 'edit'])->name('edit');
                Route::put('{paymentChannel}', [PaymentChannelController::class, 'update'])->name('update');
                Route::delete('{paymentChannel}', [PaymentChannelController::class, 'destroy'])->name('destroy');
            });

        Route::prefix('billing-tariffs')
            ->name('billing-tariffs.')
            ->group(function () {
                Route::get('/', [BillingTariffController::class, 'index'])->name('index');
                Route::get('create', [BillingTariffController::class, 'create'])->name('create');
                Route::post('/', [BillingTariffController::class, 'store'])->name('store');
                Route::get('{billingTariff}/edit', [BillingTariffController::class, 'edit'])->name('edit');
                Route::put('{billingTariff}', [BillingTariffController::class, 'update'])->name('update');
                Route::delete('{billingTariff}', [BillingTariffController::class, 'destroy'])->name('destroy');
            });

        Route::prefix('student-tariff-mappings')
            ->name('student-tariff-mappings.')
            ->group(function () {
                Route::get('/', [StudentTariffMappingController::class, 'index'])->name('index');
                Route::get('create', [StudentTariffMappingController::class, 'create'])->name('create');
                Route::post('/', [StudentTariffMappingController::class, 'store'])->name('store');
                Route::get('bulk-create', [StudentTariffMappingController::class, 'bulkCreate'])->name('bulk-create');
                Route::get('eligible-students', [StudentTariffMappingController::class, 'eligibleStudents'])->name('eligible-students');
                Route::post('bulk-store', [StudentTariffMappingController::class, 'bulkStore'])->name('bulk-store');
                Route::get('{studentTariffMapping}/edit', [StudentTariffMappingController::class, 'edit'])->name('edit');
                Route::put('{studentTariffMapping}', [StudentTariffMappingController::class, 'update'])->name('update');
                Route::delete('{studentTariffMapping}', [StudentTariffMappingController::class, 'destroy'])->name('destroy');
            });

            Route::prefix('invoices')
            ->name('invoices.')
            ->group(function () {
                Route::get('/', [InvoiceController::class, 'index'])->name('index');
                Route::get('bulk-create', [InvoiceController::class, 'bulkCreate'])->name('bulk-create');
                Route::get('eligible-students', [InvoiceController::class, 'eligibleStudents'])->name('eligible-students');
                Route::post('bulk-store', [InvoiceController::class, 'bulkStore'])->name('bulk-store');
                Route::get('manual-create', [InvoiceController::class, 'manualCreate'])->name('manual-create');
                Route::post('manual-store', [InvoiceController::class, 'manualStore'])->name('manual-store');
                Route::get('{invoice}', [InvoiceController::class, 'show'])->name('show');
                Route::delete('{invoice}', [InvoiceController::class, 'destroy'])->name('destroy');
                Route::post('{invoice}/items', [InvoiceController::class, 'storeItem'])->name('items.store');
                Route::delete('{invoice}/items/{item}', [InvoiceController::class, 'destroyItem'])->name('items.destroy');
            });

            Route::prefix('reports')
            ->name('reports.')
            ->group(function () {
                Route::get('/', [FinancialReportController::class, 'index'])->name('index');
                Route::get('monthly-recap', [FinancialReportController::class, 'monthlyRecap'])->name('monthly-recap');
                Route::get('outstanding', [FinancialReportController::class, 'outstanding'])->name('outstanding');
                Route::get('payment-channel-recap', [FinancialReportController::class, 'paymentChannelRecap'])->name('payment-channel-recap');
                Route::get('component-breakdown', [FinancialReportController::class, 'componentBreakdown'])->name('component-breakdown');
            });
    });
