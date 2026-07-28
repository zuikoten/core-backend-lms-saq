<?php

namespace Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Auth\Actions\AuthenticateAdminAction;
use Modules\Auth\Requests\LoginRequest;

class AuthController extends Controller
{
    public function showLoginForm(): View
    {
        return view('modules.auth.login');
    }

    public function login(LoginRequest $request, AuthenticateAdminAction $action): RedirectResponse
    {
        // ValidationException dari Action otomatis ditangkap Laravel dan
        // dikembalikan ke view login sebagai $errors — sesuai perilaku
        // default form request, tidak perlu try/catch manual di sini.
        $action->execute(
            $request->validated('email'),
            $request->validated('password'),
            $request->boolean('remember'),
        );

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        auth()->guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
