<?php

namespace Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Auth\Actions\UpdatePasswordAction;
use Modules\Auth\Actions\UpdateProfileAction;
use Modules\Auth\Actions\UpdateEmailAction;
use Modules\Auth\Requests\UpdateEmailRequest;
use Modules\Auth\Requests\UpdatePasswordRequest;
use Modules\Auth\Requests\UpdateProfileRequest;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('modules.auth.profile.edit', ['user' => $this->currentUser()]);
    }

    public function update(UpdateProfileRequest $request, UpdateProfileAction $action): RedirectResponse
    {
        $action->execute($this->currentUser(), $request->validated(), $request->file('avatar'));

        return back()->with('status', 'Profil berhasil diperbarui.');
    }

    public function updateEmail(UpdateEmailRequest $request, UpdateEmailAction $action): RedirectResponse
    {
        $action->execute($this->currentUser(), $request->validated()['email']);

        return back()->with('status', 'Email berhasil diganti.');
    }

    public function updatePassword(UpdatePasswordRequest $request, UpdatePasswordAction $action): RedirectResponse
    {
        $action->execute($this->currentUser(), $request->validated()['password']);

        return back()->with('status', 'Password berhasil diganti.');
    }

    private function currentUser(): User
    {
        /** @var User $user */
        $user = auth()->user();

        return $user;
    }
}
