<?php

namespace Modules\Auth\Requests\Concerns;

trait NormalizesPhoneNumber
{
    /**
     * Duplikat sengaja dari logic mutator phoneNumber() di App\Models\User
     * — bukan menggantikan mutator itu. Mutator urus format yang tersimpan
     * di database; normalisasi di sini urus supaya rule unique/exists di
     * Request membandingkan nilai dalam format yang SAMA dengan yang ada
     * di database, sebelum data itu sempat disimpan lewat mutator.
     *
     * Dipanggil dari prepareForValidation() tiap Form Request yang punya
     * field 'phone_number'.
     */
    protected function normalizePhoneNumberInput(string $field = 'phone_number'): void
    {
        if (! $this->filled($field)) {
            return;
        }

        $digits = preg_replace('/\D/', '', (string) $this->input($field));

        $normalized = str_starts_with($digits, '0')
            ? '62'.substr($digits, 1)
            : $digits;

        $this->merge([$field => $normalized]);
    }
}
