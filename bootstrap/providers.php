<?php

use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    // Daftarkan provider modul di sini:
    Modules\Auth\Providers\AuthModuleServiceProvider::class,
    Modules\Auth\Providers\AuthRateLimiterServiceProvider::class,
    Modules\Core\Providers\CoreModuleServiceProvider ::class,
    Modules\Student\Providers\StudentModuleServiceProvider::class,
    Modules\Academic\Providers\AcademicModuleServiceProvider::class,
    Modules\Finance\Providers\FinanceModuleServiceProvider::class,
];
