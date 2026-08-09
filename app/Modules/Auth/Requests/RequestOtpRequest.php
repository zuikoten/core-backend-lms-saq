<?php

namespace Modules\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Standar penyimpanan nomor HP di sistem ini: 62xxxxxxxxxx (tanpa "+", tanpa
 * spasi/strip, awalan 0 diganti 62). Normalisasi dilakukan di sini supaya
 * Action tidak perlu peduli format input dari client.
 */
class RequestOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

     public function rules(): array
    {
        $rules = [
            'phone_number' => ['required', 'string', 'regex:/^(\+?62|0)8[0-9]{8,11}$/'],
            'action_type' => ['required', 'in:activation,login,reset_password'],
        ];
 
        // Khusus activation: cegah kirim OTP kalau nomornya belum pernah
        // diinput staf sama sekali (mencegah "sukses palsu" — OTP terkirim
        // tapi nanti pasti gagal di ActivateParentAccountAction).
        if ($this->input('action_type') === 'activation') {
            $rules['phone_number'][] = Rule::exists('parents', 'phone_number')
                ->where(fn ($query) => $query->whereNull('user_id'));
        } elseif (in_array($this->input('action_type'), ['login', 'reset_password'], true)) {
            // login/reset_password: nomor harus sudah jadi akun. Bukan fix
            // bug (GenerateOtpAction::validateContext() sudah menolak kasus
            // ini juga) — ini cuma biar 3 action_type konsisten gagal di
            // layer yang sama. Pengecekan di Action TETAP dipertahankan
            // sebagai lapisan kedua, untuk pemanggilan dari luar Request ini.
            $rules['phone_number'][] = Rule::exists('users', 'phone_number');
        }
 
        return $rules;
    }
 
    public function messages(): array
    {
        return [
            'phone_number.exists' => 'Nomor belum terdaftar, hubungi pihak sekolah.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone_number' => $this->normalizePhoneNumber((string) $this->input('phone_number')),
        ]);
    }

    private function normalizePhoneNumber(string $phoneNumber): string
    {
        $digits = preg_replace('/\D/', '', $phoneNumber);

        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        }

        return $digits;
    }
}
