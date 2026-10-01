<?php

namespace Modules\Finance\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Finance\Contracts\PaymentGatewayInterface;
use Modules\Finance\Gateways\XenditPaymentGateway;

class FinanceModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PaymentGatewayInterface::class, XenditPaymentGateway::class);
    }

    public function boot(): void
    {
        Route::middleware('web')->group(__DIR__ . '/../web.php');
        Route::prefix('api')
            ->middleware('api')
            ->group(__DIR__ . '/../api.php');
    }
}
