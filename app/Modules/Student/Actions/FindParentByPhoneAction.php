<?php

namespace Modules\Student\Actions;

use Modules\Student\Models\ParentProfile;

class FindParentByPhoneAction
{
    public function execute(string $phoneNumber): ?ParentProfile
    {
        $normalized = $this->normalize($phoneNumber);

        return ParentProfile::query()
            ->where('phone_number', $normalized)
            ->with('students')
            ->first();
    }

    private function normalize(string $value): string
    {
        $digits = preg_replace('/\D/', '', $value);

        return str_starts_with($digits, '0') ? '62'.substr($digits, 1) : $digits;
    }
}