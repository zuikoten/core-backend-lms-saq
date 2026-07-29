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
use Modules\Auth\Actions\SetPasswordAfterOtpVerificationAction;
use Modules\Auth\Actions\VerifyOtpAction;
use Modules\Auth\Requests\StaffForgotPasswordRequest;
use Modules\Auth\Requests\StaffRequestOtpRequest;
use Modules\Auth\Requests\StaffResetPasswordRequest;
use Modules\Auth\Requests\StaffSetNewPasswordRequest;
use Modules\Auth\Requests\StaffVerifyOtpRequest;

class StaffPasswordResetController extends Controller
{
    /**
     * Kunci session yang menandai nomor HP mana yang OTP-nya baru saja
     * berhasil diverifikasi — dipakai sebagai "tiket" masuk ke step
     * set password baru, supaya orang tidak bisa langsung buka halaman
     * itu tanpa lewat verifikasi OTP dulu.
     */
    private const OTP_VERIFIED_SESSION_KEY = 'staff_reset_otp_verified_phone';

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
    // Jalur 2: OTP WhatsApp — dipecah 3 step: minta OTP -> verifikasi
    // OTP -> set password baru. Beda dari parent (API), yang verifikasi
    // OTP + set password digabung satu request lewat ResetPasswordWithOtpAction.
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

    public function verifyOtp(StaffVerifyOtpRequest $request, VerifyOtpAction $action): RedirectResponse
    {
        $phoneNumber = $request->validated('phone_number');

        $user = \App\Models\User::query()->where('phone_number', $phoneNumber)->first();

        if (! $user) {
            return back()->withErrors(['otp_code' => 'Akun tidak ditemukan.']);
        }

        $action->execute('reset_password', $request->validated('otp_code'), user: $user);

        session([self::OTP_VERIFIED_SESSION_KEY => $phoneNumber]);

        return redirect()->route('password.otp.new-password.form', ['phone_number' => $phoneNumber]);
    }

    public function showNewPasswordForm(Request $request): View|RedirectResponse
    {
        $phoneNumber = $request->query('phone_number');

        if (! $this->otpWasVerifiedFor($phoneNumber)) {
            return redirect()->route('password.request.otp')
                ->withErrors(['phone_number' => 'Sesi verifikasi sudah tidak berlaku, silakan ulangi dari awal.']);
        }

        return view('modules.auth.reset-password-new', ['phoneNumber' => $phoneNumber]);
    }

    public function setNewPassword(StaffSetNewPasswordRequest $request, SetPasswordAfterOtpVerificationAction $action): RedirectResponse
    {
        $phoneNumber = $request->validated('phone_number');

        if (! $this->otpWasVerifiedFor($phoneNumber)) {
            return redirect()->route('password.request.otp')
                ->withErrors(['phone_number' => 'Sesi verifikasi sudah tidak berlaku, silakan ulangi dari awal.']);
        }

        $action->execute($phoneNumber, $request->validated('password'));

        session()->forget(self::OTP_VERIFIED_SESSION_KEY);

        return redirect()->route('login')->with('status', 'Password berhasil direset, silakan login.');
    }

    private function otpWasVerifiedFor(?string $phoneNumber): bool
    {
        return $phoneNumber && session(self::OTP_VERIFIED_SESSION_KEY) === $phoneNumber;
    }
}