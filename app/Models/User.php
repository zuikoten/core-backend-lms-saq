<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'email', 'phone_number', 'password', 'is_active',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Normalisasi nomor HP ke format 62xxxxxxxxxx setiap kali di-assign,
     * dari jalur mana pun (form, seeder, Tinker, import) — bukan cuma
     * yang lewat Form Request yang sudah punya normalisasi sendiri.
     */
    protected function phoneNumber(): Attribute
    {
        return Attribute::make(
            set: function (?string $value) {
                if ($value === null) {
                    return null;
                }

                $digits = preg_replace('/\D/', '', $value);

                return str_starts_with($digits, '0')
                    ? '62'.substr($digits, 1)
                    : $digits;
            },
        );
    }
}