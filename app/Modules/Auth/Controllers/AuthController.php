<?php

namespace Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Modules\Auth\Actions\AuthenticateStaffAction;
use Modules\Auth\Actions\AuthenticateStaffWithOtpAction;
use Modules\Auth\Actions\RequestStaffLoginOtpAction;
use Modules\Auth\Requests\LoginRequest;
use Modules\Auth\Requests\StaffRequestOtpRequest;
use Modules\Auth\Requests\StaffVerifyOtpRequest;

class AuthController extends Controller
{
    public function showLoginForm(): View
    {
        return view('modules.auth.login');
    }

    public function login(LoginRequest $request, AuthenticateStaffAction $action): RedirectResponse
    {
        $action->execute(
            $request->validated('email'),
            $request->validated('password'),
            $request->boolean('remember'),
        );

        $request->session()->regenerate();

        return redirect()->intended(route('staff.dashboard'));
    }

    // ---------------------------------------------------------------
    // Login via OTP WhatsApp — jalur alternatif dari email+password,
    // dipilih lewat toggle di halaman login yang sama.
    // ---------------------------------------------------------------

    public function requestLoginOtp(StaffRequestOtpRequest $request, RequestStaffLoginOtpAction $action): RedirectResponse
    {
        $user = $action->execute($request->validated('phone_number'));

        return redirect()->route('login.otp.verify.form', ['phone_number' => $user->phone_number])
            ->with('status', 'Kode OTP telah dikirim ke nomor HP yang terdaftar.');
    }

    public function showLoginOtpVerifyForm(Request $request): View
    {
        return view('modules.auth.login-otp-verify', [
            'phoneNumber' => $request->query('phone_number'),
        ]);
    }

    public function loginWithOtp(StaffVerifyOtpRequest $request, AuthenticateStaffWithOtpAction $action): RedirectResponse
    {
        $user = $action->execute($request->validated('phone_number'), $request->validated('otp_code'));

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('staff.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        auth()->guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}