<?php

namespace Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\Actions\ActivateParentAccountAction;
use Modules\Auth\Actions\AuthenticateParentWithOtpAction;
use Modules\Auth\Actions\AuthenticateParentWithPasswordAction;
use Modules\Auth\Actions\GenerateOtpAction;
use Modules\Auth\Actions\ResetPasswordWithOtpAction;
use Modules\Auth\Actions\VerifyOtpAction;
use Modules\Auth\Requests\ParentLoginRequest;
use Modules\Auth\Requests\RequestOtpRequest;
use Modules\Auth\Requests\VerifyOtpRequest;
use Modules\Auth\Resources\AuthenticatedUserResource;

class ParentAuthController extends Controller
{
    public function requestOtp(RequestOtpRequest $request, GenerateOtpAction $action): JsonResponse
    {
        $phoneNumber = $request->validated('phone_number');
        $actionType = $request->validated('action_type');

        $user = $actionType === 'activation'
            ? null
            : User::query()->where('phone_number', $phoneNumber)->first();

        $action->execute($actionType, $phoneNumber, $user);

        return response()->json(['message' => 'Kode OTP telah dikirim.']);
    }

    public function verifyOtp(
        VerifyOtpRequest $request,
        VerifyOtpAction $verifyOtpAction,
        ActivateParentAccountAction $activateParentAccountAction,
        AuthenticateParentWithOtpAction $authenticateParentWithOtpAction,
        ResetPasswordWithOtpAction $resetPasswordWithOtpAction,
    ): JsonResponse {
        $phoneNumber = $request->validated('phone_number');
        $otpCode = $request->validated('otp_code');
        $actionType = $request->validated('action_type');

        return match ($actionType) {
            'activation' => $this->handleActivation(
                $verifyOtpAction,
                $activateParentAccountAction,
                $phoneNumber,
                $otpCode,
                $request->validated('password'),
            ),
            'login' => $this->respondWithUserAndToken(
                ...$authenticateParentWithOtpAction->execute($phoneNumber, $otpCode),
            ),
            'reset_password' => $this->handleResetPassword(
                $resetPasswordWithOtpAction,
                $phoneNumber,
                $otpCode,
                $request->validated('new_password'),
            ),
        };
    }

    public function login(ParentLoginRequest $request, AuthenticateParentWithPasswordAction $action): JsonResponse
    {
        $result = $action->execute($request->validated('phone_number'), $request->validated('password'));

        return $this->respondWithUserAndToken($result['user'], $result['token']);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Berhasil logout.']);
    }

    private function handleActivation(
        VerifyOtpAction $verifyOtpAction,
        ActivateParentAccountAction $activateParentAccountAction,
        string $phoneNumber,
        string $otpCode,
        ?string $password,
    ): JsonResponse {
        $verifyOtpAction->execute('activation', $otpCode, phoneNumber: $phoneNumber);

        $result = $activateParentAccountAction->execute($phoneNumber, $password);

        return $this->respondWithUserAndToken($result['user'], $result['token']);
    }

    private function handleResetPassword(
        ResetPasswordWithOtpAction $action,
        string $phoneNumber,
        string $otpCode,
        string $newPassword,
    ): JsonResponse {
        $action->execute($phoneNumber, $otpCode, $newPassword);

        return response()->json(['message' => 'Password berhasil direset, silakan login ulang.']);
    }

    private function respondWithUserAndToken(User $user, $token): JsonResponse
    {
        $plainToken = is_string($token) ? $token : $token->plainTextToken;

        return (new AuthenticatedUserResource($user))
            ->withToken($plainToken)
            ->response();
    }
}
