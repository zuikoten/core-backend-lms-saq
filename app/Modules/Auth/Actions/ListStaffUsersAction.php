<?php

namespace Modules\Auth\Actions;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListStaffUsersAction
{
    /**
     * Cuma ambil user dengan role ber-guard 'web' (staf) — user guard
     * 'sanctum' (parent, dan nanti kemungkinan student) sengaja di-exclude,
     * lihat catatan di UserController::index() versi lama.
     *
     * Nomor HP di database selalu format 62xxxxxxxxxx (mutator
     * phoneNumber() di App\Models\User). Search dinormalisasi ke format
     * yang sama dulu sebelum di-LIKE, supaya orang bisa cari pakai format
     * lokal (08xxx) yang wajar diketik, bukan wajib hafal format 62.
     */
    public function execute(array $filters): LengthAwarePaginator
    {
        $search = trim($filters['search'] ?? '');
        $role = $filters['role'] ?? null;

        return User::with('roles')
            ->whereHas('roles', fn ($q) => $q->where('guard_name', 'web'))
            ->when($role, function ($q) use ($role) {
                $q->whereHas('roles', fn ($q2) => $q2->where('name', $role)->where('guard_name', 'web'));
            })
            ->when($search !== '', function ($q) use ($search) {
                $normalizedPhone = $this->normalizePhoneSearch($search);

                $q->where(function ($q2) use ($search, $normalizedPhone) {
                    $q2->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$normalizedPhone}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();
    }

    private function normalizePhoneSearch(string $search): string
    {
        $digits = preg_replace('/\D/', '', $search);

        if ($digits === '') {
            return $search;
        }

        return str_starts_with($digits, '0')
            ? '62'.substr($digits, 1)
            : $digits;
    }
}
