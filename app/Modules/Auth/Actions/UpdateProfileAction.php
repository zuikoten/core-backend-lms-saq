<?php

namespace Modules\Auth\Actions;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UpdateProfileAction
{
    /**
     * Avatar disimpan di disk 'public', folder 'avatars'. Kalau user upload
     * foto baru, file lama dihapus dulu — supaya storage gak numpuk file
     * yatim yang gak lagi dipakai siapa pun begitu foto diganti berkali-kali.
     *
     * username disengaja diubah jadi null kalau dikirim string kosong
     * (bukan disimpan sebagai ''), supaya konsisten sama constraint unique
     * — banyak baris ber-username NULL itu valid, tapi banyak baris
     * ber-username '' bakal saling bentrok di constraint unique.
     */
    public function execute(User $user, array $data, ?UploadedFile $avatar = null): User
    {
        $payload = [
            'name' => $data['name'],
            'username' => $data['username'] !== null && $data['username'] !== '' ? $data['username'] : null,
        ];

        if ($avatar) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $payload['avatar'] = $avatar->store('avatars', 'public');
        }

        $user->update($payload);

        return $user;
    }
}
