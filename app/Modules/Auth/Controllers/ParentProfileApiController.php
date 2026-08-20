<?php

namespace Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Modules\Auth\Actions\ConfirmParentPhoneChangeAction;
use Modules\Auth\Actions\RequestParentPhoneChangeOtpAction;
use Modules\Auth\Actions\UpdateEmailAction;
use Modules\Auth\Actions\UpdatePasswordAction;
use Modules\Auth\Actions\UpdateProfileAction;
use Modules\Auth\Requests\ConfirmPhoneChangeRequest;
use Modules\Auth\Requests\RequestPhoneChangeOtpRequest;
use Modules\Auth\Requests\UpdateEmailRequest;
use Modules\Auth\Requests\UpdatePasswordRequest;
use Modules\Auth\Requests\UpdateProfileRequest;
use Modules\Auth\Resources\ParentProfileResource;

class ParentProfileApiController extends Controller
{
    public function show(): ParentProfileResource
    {
        return new ParentProfileResource($this->currentUser());
    }

    /**
     * name/username/avatar — Action & Request-nya reuse persis dari fitur
     * profil staf (UpdateProfileAction/UpdateProfileRequest), karena
     * keduanya cuma kerja ke User model, gak peduli guard.
     */
    public function update(UpdateProfileRequest $request, UpdateProfileAction $action): ParentProfileResource
    {
        $user = $action->execute($this->currentUser(), $request->validated(), $request->file('avatar'));

        return new ParentProfileResource($user);
    }

    public function updateEmail(UpdateEmailRequest $request, UpdateEmailAction $action): ParentProfileResource
    {
        $user = $action->execute($this->currentUser(), $request->validated()['email']);

        return new ParentProfileResource($user);
    }

    public function updatePassword(UpdatePasswordRequest $request, UpdatePasswordAction $action): JsonResponse
    {
        $action->execute($this->currentUser(), $request->validated()['password']);

        return response()->json(['message' => 'Password berhasil diganti.']);
    }

    /**
     * Ganti nomor HP TIDAK reuse UpdatePhoneRequest/UpdatePhoneAction milik
     * staf (yang cukup modal current_password) — buat parent nomor HP itu
     * kanal OTP satu-satunya, jadi wajib verifikasi OTP ke nomor baru dulu.
     * Lihat RequestParentPhoneChangeOtpAction & ConfirmParentPhoneChangeAction.
     */
    public function requestPhoneChangeOtp(RequestPhoneChangeOtpRequest $request, RequestParentPhoneChangeOtpAction $action): JsonResponse
    {
        $action->execute($this->currentUser(), $request->validated()['phone_number']);

        return response()->json(['message' => 'Kode OTP dikirim ke nomor baru.']);
    }

    public function confirmPhoneChange(ConfirmPhoneChangeRequest $request, ConfirmParentPhoneChangeAction $action): ParentProfileResource
    {
        $user = $action->execute($this->currentUser(), $request->validated()['otp_code']);

        return new ParentProfileResource($user);
    }

    private function currentUser(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }
}
