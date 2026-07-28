<?php

namespace Modules\Auth\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->is_active) {
            if ($request->hasSession()) {
                auth()->guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            if ($user->currentAccessToken()) {
                $user->currentAccessToken()->delete();
            }

            abort_if($request->expectsJson(), 403, 'Akun Anda tidak aktif, hubungi pihak sekolah.');

            return redirect()->route('login')->withErrors([
                'email' => 'Akun Anda tidak aktif, hubungi pihak sekolah.',
            ]);
        }

        return $next($request);
    }
}
