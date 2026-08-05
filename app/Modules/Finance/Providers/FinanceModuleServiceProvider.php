<?php

namespace Modules\Finance\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class FinanceModuleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Route::middleware('web')->group(__DIR__.'/../web.php');

        // api.php belum ada — BillingType & PaymentChannel murni staf-only
        // (master data), belum ada konsumen dari sisi orang tua. Tambahkan
        // Route::prefix('api')->middleware('api')->group(...) di sini nanti
        // begitu modul Finance sampai ke Invoice yang perlu dilihat orang tua.
    }
}
