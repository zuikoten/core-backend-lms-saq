<?php

namespace Modules\Auth\Controllers;

use Modules\Auth\Actions\AuthenticateAdminAction;
use Modules\Auth\Requests\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController
{
    public function showLoginForm(): View
    {
        return view('modules.auth.login');
    }

    public function login(LoginRequest $request, AuthenticateAdminAction $action): RedirectResponse
    {
        $action->execute(
            $request->validated('email'),
            $request->validated('password'),
            $request->boolean('remember')
        );

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(): RedirectResponse
    {
        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    }
}