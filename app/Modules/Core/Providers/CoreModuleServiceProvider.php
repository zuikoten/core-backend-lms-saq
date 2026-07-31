<?php

namespace Modules\Core\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class CoreModuleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerRoutes();
    }

    private function registerRoutes(): void
    {
        Route::middleware('web')
            ->group(__DIR__.'/../web.php');

        Route::prefix('api')
            ->middleware('api')
            ->group(__DIR__.'/../api.php');
    }
}
