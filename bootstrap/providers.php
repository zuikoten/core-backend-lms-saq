<?php

use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    // Daftarkan provider modul di sini:
    Modules\Auth\Providers\AuthModuleServiceProvider::class,
];
