<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Middleware\EnsureUserIsActive;
use Modules\Finance\Controllers\BillingTariffController;
use Modules\Finance\Controllers\BillingTypeController;
use Modules\Finance\Controllers\PaymentChannelController;

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
    });