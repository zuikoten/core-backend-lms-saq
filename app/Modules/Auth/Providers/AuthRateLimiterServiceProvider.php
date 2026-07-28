<?php

namespace Modules\Auth\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AuthRateLimiterServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Cegah spam yang membebani biaya WA Gateway.
        RateLimiter::for('otp-request', function (Request $request) {
            $key = $request->input('phone_number', $request->ip());

            return Limit::perMinute(3)->by('otp-request:'.$key);
        });

        // Cegah brute force login, dipakai baik oleh route login admin (web.php)
        // maupun login parent (api.php).
        RateLimiter::for('login', function (Request $request) {
            $key = $request->input('email') ?? $request->input('phone_number') ?? $request->ip();

            return Limit::perMinute(10)->by('login:'.$key);
        });
    }
}
