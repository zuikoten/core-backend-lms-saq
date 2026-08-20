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

            return Limit::perMinute(3)->by('otp-request:' . $key);
        });

        // Cegah brute force login, dipakai baik oleh route login admin (web.php)
        // maupun login parent (api.php).
        RateLimiter::for('login', function (Request $request) {
            $key = $request->input('email') ?? $request->input('phone_number') ?? $request->ip();

            return Limit::perMinute(10)->by('login:' . $key);
        });

        // Cegah brute force tebak "password saat ini" lewat form Ganti Email/HP/
        // Password di halaman Profil. Beda dari limiter 'login': konteksnya
        // SUDAH login, jadi identitas akun sudah pasti dari auth()->id(), bukan
        // ditebak dari email/nomor HP di body request (yang gak selalu ada,
        // contoh: route ganti password gak punya field email/phone sama sekali).
        RateLimiter::for('sensitive-profile-update', function (Request $request) {
            return Limit::perMinute(10)->by('sensitive-profile-update:' . $request->user()->id);
        });
    }
}
