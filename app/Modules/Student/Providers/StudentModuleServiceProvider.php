<?php

namespace Modules\Student\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class StudentModuleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Route::middleware('web')->group(__DIR__.'/../web.php');

        Route::prefix('api')
            ->middleware('api')
            ->group(__DIR__.'/../api.php');
    }
}