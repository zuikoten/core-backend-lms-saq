<?php

namespace Modules\Auth\Actions;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UpdateProfileAction
{
    private const AVATAR_SIZE = 300;

    /**
     * Avatar disimpan di disk 'public', folder 'avatars', selalu dikonversi
     * ke PNG 300x300 gak peduli format/ukuran aslinya — supaya ukuran file
     * konsisten kecil & seragam, gak tergantung foto asli user berapa MB.
     *
     * File lama dihapus dulu sebelum yang baru disimpan, supaya storage
     * gak numpuk file yatim tiap kali user ganti foto berkali-kali.
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

            $payload['avatar'] = $this->storeCompressedAvatar($avatar, $user);
        }

        $user->update($payload);

        return $user;
    }

    /**
     * Crop ke persegi dari tengah (ambil sisi terpendek), baru di-resize
     * ke 300x300 — supaya foto non-persegi gak gepeng, mirip pola crop
     * avatar di kebanyakan aplikasi (Google, GitHub, dll).
     */
    private function storeCompressedAvatar(UploadedFile $file, User $user): string
    {
        [$width, $height, $type] = getimagesize($file->getRealPath());

        $source = match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($file->getRealPath()),
            IMAGETYPE_PNG => imagecreatefrompng($file->getRealPath()),
            IMAGETYPE_WEBP => imagecreatefromwebp($file->getRealPath()),
            default => throw new \RuntimeException('Format gambar tidak didukung.'),
        };

        $squareSize = min($width, $height);
        $srcX = (int) (($width - $squareSize) / 2);
        $srcY = (int) (($height - $squareSize) / 2);

        $target = imagecreatetruecolor(self::AVATAR_SIZE, self::AVATAR_SIZE);
        imagealphablending($target, false);
        imagesavealpha($target, true);
        $transparent = imagecolorallocatealpha($target, 0, 0, 0, 127);
        imagefilledrectangle($target, 0, 0, self::AVATAR_SIZE, self::AVATAR_SIZE, $transparent);

        imagecopyresampled(
            $target, $source,
            0, 0, $srcX, $srcY,
            self::AVATAR_SIZE, self::AVATAR_SIZE, $squareSize, $squareSize
        );

        ob_start();
        imagepng($target, null, 6); // level 6: kompres cukup, kualitas masih bagus
        $binary = ob_get_clean();

        imagedestroy($source);
        imagedestroy($target);

        // uniqid() di nama file — mencegah browser nampilin foto lama dari
        // cache kalau user ganti-ganti avatar dengan nama file yang sama.
        $filename = "avatars/{$user->id}-".uniqid().'.png';
        Storage::disk('public')->put($filename, $binary);

        return $filename;
    }
}
