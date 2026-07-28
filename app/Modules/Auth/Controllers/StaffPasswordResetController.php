<?php

namespace Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Modules\Auth\Actions\RequestStaffPasswordResetOtpAction;
use Modules\Auth\Actions\ResetPasswordWithOtpAction;
use Modules\Auth\Requests\StaffForgotPasswordRequest;
use Modules\Auth\Requests\StaffRequestOtpRequest;
use Modules\Auth\Requests\StaffResetPasswordRequest;
use Modules\Auth\Requests\StaffVerifyOtpRequest;

class StaffPasswordResetController extends Controller
{
    /**
     * Halaman pilihan: reset via email atau via OTP WhatsApp.
     */
    public function showChooseForm(): View
    {
        return view('modules.auth.forgot-password');
    }

    // ---------------------------------------------------------------
    // Jalur 1: broker email bawaan Laravel (link ke inbox)
    // ---------------------------------------------------------------

    public function showLinkRequestForm(): View
    {
        return view('modules.auth.forgot-password-email');
    }

    public function sendResetLinkEmail(StaffForgotPasswordRequest $request): RedirectResponse
    {
        $status = Password::broker()->sendResetLink(
            $request->only('email'),
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    public function showResetForm(string $token, Request $request): View
    {
        return view('modules.auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function reset(StaffResetPasswordRequest $request): RedirectResponse
    {
        $status = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }

    // ---------------------------------------------------------------
    // Jalur 2: OTP WhatsApp (alternatif, pakai infrastruktur otp_codes
    // yang sama dengan parent — lihat RequestStaffPasswordResetOtpAction
    // & ResetPasswordWithOtpAction)
    // ---------------------------------------------------------------

    public function showOtpRequestForm(): View
    {
        return view('modules.auth.forgot-password-otp');
    }

    public function requestOtp(StaffRequestOtpRequest $request, RequestStaffPasswordResetOtpAction $action): RedirectResponse
    {
        $user = $action->execute($request->validated('phone_number'));

        return redirect()->route('password.otp.verify.form', ['phone_number' => $user->phone_number])
            ->with('status', 'Kode OTP telah dikirim ke nomor HP yang terdaftar.');
    }

    public function showOtpVerifyForm(Request $request): View
    {
        return view('modules.auth.reset-password-otp', [
            'phoneNumber' => $request->query('phone_number'),
        ]);
    }

    public function resetWithOtp(StaffVerifyOtpRequest $request, ResetPasswordWithOtpAction $action): RedirectResponse
    {
        $action->execute(
            $request->validated('phone_number'),
            $request->validated('otp_code'),
            $request->validated('password'),
        );

        return redirect()->route('login')->with('status', 'Password berhasil direset, silakan login.');
    }
}
